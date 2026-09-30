<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['deal:id,title', 'client:id,name'])
            ->whereNull('parent_id') // top-level only
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filter === 'today', fn ($q) => $q->whereDate('due_date', now()->toDateString())
                ->whereIn('status', ['incomplete', 'in_progress']))
            ->when($request->filter === 'upcoming', fn ($q) => $q->whereDate('due_date', '>', now()->toDateString())
                ->whereIn('status', ['incomplete', 'in_progress']))
            ->when($request->filter === 'overdue', fn ($q) => $q->whereDate('due_date', '<', now()->toDateString())
                ->whereIn('status', ['incomplete', 'in_progress']))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->search, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->orderByRaw("FIELD(status, 'in_progress', 'incomplete', 'complete', 'closed')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('due_date');

        return response()->json($query->paginate(25));
    }

    public function summary()
    {
        $today = now()->toDateString();
        $isAdmin = auth()->user()->isSuperAdmin();

        $base = fn () => Task::whereNull('parent_id')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', auth()->id()));

        $active = fn () => $base()->whereIn('status', ['incomplete', 'in_progress']);

        return response()->json([
            'overdue' => $active()->whereDate('due_date', '<', $today)->count(),
            'today' => $active()->whereDate('due_date', $today)->count(),
            'upcoming' => $active()->whereDate('due_date', '>', $today)->count(),
            'total' => $base()->count(),
        ]);
    }

    public function grouped()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $weekEnd = now()->addDays(7)->toDateString();
        $isAdmin = auth()->user()->isSuperAdmin();

        $base = fn () => Task::with(['deal:id,title', 'client:id,name'])
            ->withCount(['subtasks', 'notes'])
            ->whereNull('parent_id')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', auth()->id()))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('due_date');

        // incomplete + in_progress both show in the timeline sections
        $active = fn () => $base()->whereIn('status', ['incomplete', 'in_progress']);

        return response()->json([
            'overdue' => $active()->whereDate('due_date', '<', $today)->get(),
            'today' => $active()->whereDate('due_date', $today)->get(),
            'tomorrow' => $active()->whereDate('due_date', $tomorrow)->get(),
            'this_week' => $active()
                ->whereDate('due_date', '>', $tomorrow)
                ->whereDate('due_date', '<=', $weekEnd)
                ->get(),
            'later' => $active()->whereDate('due_date', '>', $weekEnd)->get(),
            'complete' => $base()->where('status', 'complete')
                ->orderByDesc('updated_at')->limit(20)->get(),
            'closed' => $base()->where('status', 'closed')
                ->orderByDesc('updated_at')->limit(20)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:incomplete,in_progress,complete,closed',
            'deal_id' => 'nullable|exists:deals,id',
            'client_id' => 'nullable|exists:clients,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'required_if:is_recurring,true|nullable|in:daily,weekly,biweekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1|max:99',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'integer|min:0|max:6',
            'recurrence_end_date' => 'nullable|date|after:due_date',
        ]);

        $task = Task::create(array_merge($data, ['user_id' => auth()->id()]));

        return response()->json(
            $task->load(['deal:id,title', 'client:id,name']),
            201
        );
    }

    public function show(Task $task)
    {
        $this->gate($task);

        return response()->json(
            $task->load([
                'deal:id,title',
                'client:id,name',
                'notes.user:id,name',
                'subtasks.deal:id,title',
                'subtasks.client:id,name',
                'parent:id,title',
                'occurrences.user:id,name',
            ])
        );
    }

    public function update(Request $request, Task $task)
    {
        $this->gate($task);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:incomplete,in_progress,complete,closed',
            'deal_id' => 'nullable|exists:deals,id',
            'client_id' => 'nullable|exists:clients,id',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,biweekly,monthly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1|max:99',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'integer|min:0|max:6',
            'recurrence_end_date' => 'nullable|date',
        ]);

        $previousStatus = $task->status;
        $task->update($data);

        // Auto-generate next recurring instance when marked complete
        if (
            isset($data['status']) &&
            $data['status'] === 'complete' &&
            $previousStatus !== 'complete' &&
            $task->shouldGenerateNext()
        ) {
            $this->generateNextRecurrence($task);
        }

        return response()->json($task->load(['deal:id,title', 'client:id,name']));
    }

    public function destroy(Task $task)
    {
        $this->gate($task);
        // Delete subtasks too
        $task->subtasks()->delete();
        $task->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    // ─── Subtasks ────────────────────────────────────────────────
    public function storeSubtask(Request $request, Task $task)
    {
        $this->gate($task);
        abort_if($task->parent_id !== null, 422, 'Subtasks cannot have subtasks.');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'status' => 'required|in:incomplete,in_progress,complete,closed',
        ]);

        $subtask = Task::create(array_merge($data, [
            'user_id' => auth()->id(),
            'parent_id' => $task->id,
            'client_id' => $task->client_id,
            'deal_id' => $task->deal_id,
        ]));

        return response()->json($subtask, 201);
    }

    // ─── Recurring occurrences ───────────────────────────────────
    public function completeOccurrence(Request $request, Task $task)
    {
        $this->gate($task);
        abort_unless($task->is_recurring, 422, 'This task is not recurring.');

        $data = $request->validate([
            'note' => 'nullable|string|max:2000',
        ]);

        // Log occurrence
        $task->occurrences()->create([
            'user_id' => auth()->id(),
            'due_date' => $task->due_date,
            'note' => $data['note'] ?? null,
            'completed_at' => now(),
        ]);

        // Advance schedule
        $nextDate = $task->nextDueDate();

        if (! $nextDate || ($task->recurrence_end_date && $nextDate->gt($task->recurrence_end_date))) {
            $task->update(['status' => 'complete']);
        } else {
            $task->update([
                'due_date' => $nextDate,
                'status' => 'incomplete',
            ]);
        }

        return response()->json(
            $task->fresh()->load([
                'deal:id,title',
                'client:id,name',
                'notes.user:id,name',
                'subtasks',
                'parent:id,title',
                'occurrences.user:id,name',
            ])
        );
    }

    // ─── Notes ───────────────────────────────────────────────────
    public function addNote(Request $request, Task $task)
    {
        $this->gate($task);

        $data = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note = $task->notes()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return response()->json($note->load('user:id,name'), 201);
    }

    public function deleteNote(Task $task, Note $note)
    {
        $this->gate($task);
        abort_if(
            $note->notable_type !== Task::class || $note->notable_id !== $task->id,
            404
        );
        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    // ─── Recurrence ──────────────────────────────────────────────
    private function generateNextRecurrence(Task $original): void
    {
        $nextDate = $original->nextDueDate();
        if (! $nextDate) {
            return;
        }

        Task::create([
            'user_id' => $original->user_id,
            'client_id' => $original->client_id,
            'deal_id' => $original->deal_id,
            'parent_id' => $original->parent_id,
            'title' => $original->title,
            'description' => $original->description,
            'due_date' => $nextDate,
            'priority' => $original->priority,
            'status' => 'incomplete',
            'is_recurring' => true,
            'recurrence_type' => $original->recurrence_type,
            'recurrence_interval' => $original->recurrence_interval,
            'recurrence_days' => $original->recurrence_days,
            'recurrence_end_date' => $original->recurrence_end_date,
            'recurring_parent_id' => $original->recurring_parent_id ?? $original->id,
        ]);
    }

    private function gate(Task $task): void
    {
        if (! auth()->user()->isSuperAdmin() && $task->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
