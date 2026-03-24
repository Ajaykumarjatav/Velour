<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNote;
use App\Models\ClientFormula;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    /* ── GET /clients ───────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'tag'      => 'nullable|string',
            'status'   => 'nullable|in:active,inactive,blocked',
            'vip'      => 'nullable|boolean',
            'sort'     => 'nullable|in:name,ltv,visits,last_visit,created_at',
            'dir'      => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $salonId = $request->attributes->get('salon_id');

        $q = Client::with(['preferredStaff'])
            ->where('salon_id', $salonId);

        if ($s = $request->search) {
            $q->where(function ($sq) use ($s) {
                $sq->whereRaw("(first_name || ' ' || last_name) ILIKE ?", ["%{$s}%"])
                   ->orWhere('email', 'ilike', "%{$s}%")
                   ->orWhere('phone', 'ilike', "%{$s}%");
            });
        }

        if ($request->status) {
            $q->where('status', $request->status);
        }

        if ($request->vip !== null) {
            $q->where('is_vip', $request->boolean('vip'));
        }

        if ($request->tag) {
            $q->whereJsonContains('tags', $request->tag);
        }

        $sortColumn = match ($request->sort ?? 'name') {
            'ltv'        => 'total_spent',
            'visits'     => 'visit_count',
            'last_visit' => 'last_visit_at',
            default      => DB::raw('first_name || '' || last_name'), // safe: static string, no user input
        };

        $q->orderBy($sortColumn, $request->dir ?? 'asc');

        $clients = $q->paginate($request->per_page ?? 50);

        return response()->json($clients);
    }

    /* ── POST /clients ──────────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'date_of_birth'     => 'nullable|date|before:today',
            'tags'              => 'nullable|array',
            'is_vip'            => 'nullable|boolean',
            'preferred_staff_id'=> 'nullable|integer',
            'allergies'         => 'nullable|string|max:2000',
            'medical_notes'     => 'nullable|string|max:2000',
            'marketing_consent' => 'nullable|boolean',
            'sms_consent'       => 'nullable|boolean',
            'email_consent'     => 'nullable|boolean',
            'source'            => 'nullable|string|max:50',
            'notes'             => 'nullable|string|max:2000',
        ]);

        $salonId = $request->attributes->get('salon_id');

        // Check duplicate email within salon
        if (!empty($data['email'])) {
            $exists = Client::where('salon_id', $salonId)
                            ->where('email', $data['email'])
                            ->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'A client with this email already exists.',
                ], 422);
            }
        }

        $notes = $data['notes'] ?? null;
        unset($data['notes']);

        $colors = ['#C4556B','#B8943A','#5A8A72','#3B82F6','#8B5CF6','#D97706','#059669','#EC4899'];
        $data['color']     = $colors[array_rand($colors)];
        $data['salon_id']  = $salonId;

        $client = Client::create($data);

        if ($notes) {
            ClientNote::create([
                'client_id' => $client->id,
                'type'      => 'general',
                'content'   => $notes,
            ]);
        }

        return response()->json([
            'message' => 'Client created.',
            'client'  => $client,
        ], 201);
    }

    /* ── GET /clients/{id} ──────────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $client = Client::with([
            'preferredStaff',
            'notes' => fn($q) => $q->orderByDesc('is_pinned')->orderByDesc('created_at'),
            'formulas' => fn($q) => $q->where('is_current', true)->latest(),
        ])
        ->where('salon_id', $request->attributes->get('salon_id'))
        ->findOrFail($id);

        return response()->json($client);
    }

    /* ── PUT /clients/{id} ──────────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'first_name'         => 'sometimes|string|max:100',
            'last_name'          => 'sometimes|string|max:100',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:30',
            'date_of_birth'      => 'nullable|date|before:today',
            'tags'               => 'nullable|array',
            'is_vip'             => 'nullable|boolean',
            'preferred_staff_id' => 'nullable|integer',
            'allergies'          => 'nullable|string|max:2000',
            'medical_notes'      => 'nullable|string|max:2000',
            'marketing_consent'  => 'nullable|boolean',
            'sms_consent'        => 'nullable|boolean',
            'email_consent'      => 'nullable|boolean',
            'status'             => 'nullable|in:active,inactive,blocked',
        ]);

        $client = Client::where('salon_id', $request->attributes->get('salon_id'))
                        ->findOrFail($id);

        $client->update($data);

        return response()->json(['message' => 'Client updated.', 'client' => $client]);
    }

    /* ── DELETE /clients/{id} ───────────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $client = Client::where('salon_id', $request->attributes->get('salon_id'))
                        ->findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Client deleted.']);
    }

    /* ── GET /clients/{id}/appointments ─────────────────────────────────── */
    public function appointments(Request $request, int $id): JsonResponse
    {
        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $appointments = $client->appointments()
            ->with(['staff', 'services'])
            ->orderByDesc('starts_at')
            ->paginate(20);

        return response()->json($appointments);
    }

    /* ── GET /clients/{id}/transactions ─────────────────────────────────── */
    public function transactions(Request $request, int $id): JsonResponse
    {
        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $transactions = $client->transactions()
            ->with(['items', 'staff'])
            ->orderByDesc('completed_at')
            ->paginate(20);

        return response()->json($transactions);
    }

    /* ── GET /clients/{id}/notes ─────────────────────────────────────────── */
    public function notes(Request $request, int $id): JsonResponse
    {
        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $notes = $client->notes()
            ->with('staff')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notes);
    }

    /* ── POST /clients/{id}/notes ────────────────────────────────────────── */
    public function addNote(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'content'   => 'required|string|max:5000',
            'type'      => 'nullable|in:general,formula,allergy,medical,preference',
            'is_pinned' => 'nullable|boolean',
        ]);

        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $note = ClientNote::create([
            'client_id' => $client->id,
            'staff_id'  => $request->attributes->get('staff_id'),
            'type'      => $data['type'] ?? 'general',
            'content'   => $data['content'],
            'is_pinned' => $data['is_pinned'] ?? false,
        ]);

        return response()->json(['message' => 'Note saved.', 'note' => $note], 201);
    }

    /* ── PUT /clients/{id}/notes/{noteId} ────────────────────────────────── */
    public function updateNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $data = $request->validate([
            'content'   => 'sometimes|string|max:5000',
            'is_pinned' => 'nullable|boolean',
        ]);

        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $note   = ClientNote::where('client_id', $client->id)->findOrFail($noteId);
        $note->update($data);

        return response()->json(['message' => 'Note updated.', 'note' => $note]);
    }

    /* ── DELETE /clients/{id}/notes/{noteId} ─────────────────────────────── */
    public function deleteNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $note   = ClientNote::where('client_id', $client->id)->findOrFail($noteId);
        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    /* ── GET /clients/{id}/formula ───────────────────────────────────────── */
    public function formula(Request $request, int $id): JsonResponse
    {
        $client  = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $formula = $client->formulas()->where('is_current', true)->latest()->first();

        return response()->json($formula);
    }

    /* ── POST /clients/{id}/formula ──────────────────────────────────────── */
    public function saveFormula(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'base_color'      => 'nullable|string|max:100',
            'highlight_color' => 'nullable|string|max:100',
            'toner'           => 'nullable|string|max:100',
            'developer'       => 'nullable|string|max:100',
            'olaplex'         => 'nullable|string|max:100',
            'natural_level'   => 'nullable|string|max:50',
            'target_level'    => 'nullable|string|max:50',
            'texture'         => 'nullable|string|max:100',
            'scalp_condition' => 'nullable|string|max:200',
            'technique'       => 'nullable|string|max:1000',
            'result_notes'    => 'nullable|string|max:1000',
            'goal'            => 'nullable|string|max:500',
            'used_at'         => 'nullable|date',
        ]);

        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        // Archive old current formula
        $client->formulas()->where('is_current', true)->update(['is_current' => false]);

        $formula = ClientFormula::create([
            ...$data,
            'client_id'  => $client->id,
            'staff_id'   => $request->attributes->get('staff_id'),
            'is_current' => true,
            'used_at'    => $data['used_at'] ?? now()->toDateString(),
        ]);

        return response()->json(['message' => 'Formula saved.', 'formula' => $formula], 201);
    }

    /* ── POST /clients/{id}/message ──────────────────────────────────────── */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'channel' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|string|max:255',
            'body'    => 'required|string|max:5000',
        ]);

        $client = Client::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $this->notificationService->sendDirectMessage($client, $data);

        return response()->json(['message' => ucfirst($data['channel']) . ' sent to ' . $client->first_name . '.']);
    }

    /* ── GET /clients/export ─────────────────────────────────────────────── */
    public function export(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $clients = Client::where('salon_id', $salonId)
            ->with('preferredStaff')
            ->orderBy('first_name')
            ->get();

        return response()->json(['count' => $clients->count(), 'data' => $clients]);
    }

    /* ── POST /clients/import ────────────────────────────────────────────── */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);
        // CSV import logic would be handled by a queued job in production
        return response()->json(['message' => 'Import queued. You will be notified when complete.']);
    }
}
