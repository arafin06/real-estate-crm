<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Note;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::with(['client:id,name', 'property:id,title'])
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->stage, fn ($q, $v) => $q->where('stage', $v))
            ->when($request->search, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->latest();

        return response()->json($query->paginate(20));
    }

    public function kanban()
    {
        $deals = Deal::with(['client:id,name', 'property:id,title'])
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('created_at')
            ->get();

        $stages = ['lead', 'prospect', 'showing', 'offer_made', 'under_contract', 'closed', 'lost'];
        $grouped = [];

        foreach ($stages as $stage) {
            $grouped[$stage] = $deals->where('stage', $stage)->values();
        }

        return response()->json($grouped);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'stage' => 'required|in:lead,prospect,showing,offer_made,under_contract,closed,lost',
            'deal_value' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'lost_reason' => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = auth()->id();
        $data['commission_amount'] = round(
            floatval($data['deal_value']) * floatval($data['commission_rate']) / 100, 2
        );

        // A deal can be created already closed — without this it would count
        // toward closed_deals but stay invisible to every closed_at report.
        if ($data['stage'] === 'closed') {
            $data['closed_at'] = now();
        }

        $deal = Deal::create($data);

        $deal->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'description' => 'Deal created in stage: '.ucwords(str_replace('_', ' ', $deal->stage)),
        ]);

        return response()->json(
            $deal->load(['client:id,name', 'property:id,title', 'activities.user:id,name']),
            201
        );
    }

    public function show(Deal $deal)
    {
        $this->gate($deal);

        return response()->json(
            $deal->load([
                // No 'phone' column on clients — numbers live in client_phones.
                'client:id,name,email,type',
                'client.phones',
                'property:id,title,price,city,state,status,listing_type',
                'property.primaryImage',
                'activities.user:id,name',
                'noteEntries.user:id,name',
                'tasks' => fn ($q) => $q->whereNull('parent_id')
                    ->orderByRaw("FIELD(status, 'in_progress', 'incomplete', 'complete', 'closed')")
                    ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                    ->orderBy('due_date'),
            ])
        );
    }

    public function update(Request $request, Deal $deal)
    {
        $this->gate($deal);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'client_id' => 'sometimes|required|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'stage' => 'sometimes|required|in:lead,prospect,showing,offer_made,under_contract,closed,lost',
            'deal_value' => 'sometimes|required|numeric|min:0',
            'commission_rate' => 'sometimes|required|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'lost_reason' => 'nullable|string|max:1000',
        ]);

        // Recalculate commission
        $value = $data['deal_value'] ?? $deal->deal_value;
        $rate = $data['commission_rate'] ?? $deal->commission_rate;
        $data['commission_amount'] = round(floatval($value) * floatval($rate) / 100, 2);

        // Log stage change
        if (isset($data['stage']) && $data['stage'] !== $deal->stage) {
            $oldLabel = ucwords(str_replace('_', ' ', $deal->stage));
            $newLabel = ucwords(str_replace('_', ' ', $data['stage']));

            if ($data['stage'] === 'closed') {
                $data['closed_at'] = now();
            } else {
                $data['closed_at'] = null; // moved out of closed — clear it
            }

            $deal->update($data);
            $deal->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'stage_changed',
                'description' => "Stage changed from {$oldLabel} to {$newLabel}",
            ]);
        } else {
            $deal->update($data);
        }

        return response()->json($deal->load(['client:id,name', 'property:id,title']));
    }

    public function destroy(Deal $deal)
    {
        $this->gate($deal);
        $deal->delete();

        return response()->json(['message' => 'Deal deleted.']);
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $this->gate($deal);

        $data = $request->validate([
            'stage' => 'required|in:lead,prospect,showing,offer_made,under_contract,closed,lost',
        ]);

        if ($data['stage'] === $deal->stage) {
            return response()->json($deal);
        }

        $oldLabel = ucwords(str_replace('_', ' ', $deal->stage));
        $newLabel = ucwords(str_replace('_', ' ', $data['stage']));

        $updateData = ['stage' => $data['stage']];

        if ($data['stage'] === 'closed') {
            $updateData['closed_at'] = now();
        } else {
            $updateData['closed_at'] = null; // moved out of closed — clear it
        }

        $deal->update($updateData);

        $deal->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'stage_changed',
            'description' => "Stage moved from {$oldLabel} to {$newLabel}",
        ]);

        return response()->json($deal);
    }

    // --- Notes ---

    public function addNote(Request $request, Deal $deal)
    {
        $this->gate($deal);

        $data = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note = $deal->noteEntries()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return response()->json($note->load('user:id,name'), 201);
    }

    public function deleteNote(Deal $deal, Note $note)
    {
        $this->gate($deal);
        abort_if(
            $note->notable_type !== Deal::class || $note->notable_id !== $deal->id,
            404
        );
        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    private function gate(Deal $deal): void
    {
        if (! auth()->user()->isSuperAdmin() && $deal->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
