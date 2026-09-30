<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPhone;
use App\Models\Note;
use App\Rules\ValidPhoneNumber;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query()
            ->with('phones')
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('email', 'like', "%{$v}%")
                    ->orWhereHas('phones', function ($q) use ($v) {
                        $digits = PhoneNumber::normalize($v) ?? $v;
                        $q->where('phone', 'like', "%{$digits}%");
                    });
            }))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->latest();

        return response()->json($query->paginate(min((int) $request->input('per_page', 20), 100)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:buyer,seller,both',
            'source' => 'nullable|string|max:100',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => [
                'nullable', 'numeric', 'min:0',
                // gte only applies when a minimum was actually supplied; a
                // max-only budget would otherwise fail against a null field.
                Rule::when($request->filled('budget_min'), ['gte:budget_min']),
            ],
            'preferred_contact' => 'nullable|in:email,phone,text',
            'timeline' => 'nullable|in:asap,1_3_months,3_6_months,6_12_months,12_plus_months',
            'pre_approval_amount' => 'nullable|numeric|min:0',
            'phones' => 'nullable|array',
            'phones.*.type' => 'nullable|string|max:30',
            'phones.*.phone' => ['required_with:phones', new ValidPhoneNumber],
        ]);

        $client = DB::transaction(function () use ($data) {
            $client = Client::create([
                ...array_diff_key($data, ['phones' => null]),
                'user_id' => auth()->id(),
            ]);

            foreach ($data['phones'] ?? [] as $phone) {
                $client->phones()->create([
                    'type' => $phone['type'] ?: 'mobile',
                    'phone' => $phone['phone'],
                ]);
            }

            return $client;
        });

        return response()->json($client->load('phones'), 201);
    }

    public function show(Client $client)
    {
        $this->gate($client);

        $client->load([
            'notes.user:id,name',
            'phones',
            'deals' => fn ($q) => $q->with('property:id,title,price,city,state')
                ->select(
                    'id', 'client_id', 'user_id', 'property_id', 'title', 'stage',
                    'deal_value', 'commission_amount', 'expected_close_date', 'created_at'
                )->orderByDesc('created_at'),
            // reorder() clears the due_date sort baked into the tasks() relation,
            // which would otherwise take precedence over the clauses below.
            'tasks' => fn ($q) => $q->with('deal:id,title')
                ->whereNull('parent_id')
                ->reorder()
                ->orderByRaw("FIELD(status, 'in_progress', 'incomplete', 'complete', 'closed')")
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                ->orderBy('due_date'),
        ]);

        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $this->gate($client);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'type' => 'sometimes|required|in:buyer,seller,both',
            'source' => 'nullable|string|max:100',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => [
                'nullable', 'numeric', 'min:0',
                Rule::when($request->filled('budget_min'), ['gte:budget_min']),
            ],
            'preferred_contact' => 'nullable|in:email,phone,text',
            'timeline' => 'nullable|in:asap,1_3_months,3_6_months,6_12_months,12_plus_months',
            'pre_approval_amount' => 'nullable|numeric|min:0',
            'phones' => 'nullable|array',
            'phones.*.type' => 'nullable|string|max:30',
            'phones.*.phone' => ['required_with:phones', new ValidPhoneNumber],
        ]);

        DB::transaction(function () use ($data, $client) {
            $client->update(array_diff_key($data, ['phones' => null]));

            if (array_key_exists('phones', $data)) {
                $client->phones()->delete();
                foreach ($data['phones'] ?? [] as $phone) {
                    $client->phones()->create([
                        'type' => $phone['type'] ?: 'mobile',
                        'phone' => $phone['phone'],
                    ]);
                }
            }
        });

        return response()->json($client->load('phones'));
    }

    public function destroy(Client $client)
    {
        $this->gate($client);
        $client->delete();

        return response()->json(['message' => 'Client deleted.']);
    }

    // --- Notes ---

    public function addNote(Request $request, Client $client)
    {
        $this->gate($client);

        $data = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note = $client->notes()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return response()->json($note->load('user:id,name'), 201);
    }

    public function deleteNote(Client $client, Note $note)
    {
        $this->gate($client);
        abort_if(
            $note->notable_type !== Client::class || $note->notable_id !== $client->id,
            404
        );
        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    // --- Phones ---

    public function addPhone(Request $request, Client $client)
    {
        $this->gate($client);

        $data = $request->validate([
            'type' => 'nullable|string|max:30',
            'phone' => ['required', new ValidPhoneNumber],
        ]);

        $phone = $client->phones()->create([
            'type' => $data['type'] ?: 'mobile',
            'phone' => $data['phone'],
        ]);

        return response()->json($phone, 201);
    }

    public function updatePhone(Request $request, Client $client, ClientPhone $phone)
    {
        $this->gate($client);
        abort_if($phone->client_id !== $client->id, 404);

        $data = $request->validate([
            'type' => 'nullable|string|max:30',
            'phone' => ['required', new ValidPhoneNumber],
        ]);

        $phone->update([
            'type' => $data['type'] ?: 'mobile',
            'phone' => $data['phone'],
        ]);

        return response()->json($phone);
    }

    public function deletePhone(Client $client, ClientPhone $phone)
    {
        $this->gate($client);
        abort_if($phone->client_id !== $client->id, 404);

        $phone->delete();

        return response()->json(['message' => 'Phone number deleted.']);
    }

    private function gate(Client $client): void
    {
        if (! auth()->user()->isSuperAdmin() && $client->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
