<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GDPR Compliance Controller
 *
 * Implements:
 * - Article 17: Right to Erasure (right to be forgotten)
 * - Article 20: Data Portability (personal data export)
 * - Article 7:  Consent management
 *
 * Routes are authenticated and salon-scoped.
 */
class GdprController extends Controller
{
    /* ── POST /salon/gdpr/clients/{client}/erase ─────────────────────── */
    /**
     * Anonymise a client's personal data (GDPR Article 17 — Right to Erasure).
     * Retains anonymised financial records for legal/tax obligations (Article 17.3b).
     */
    public function erase(Request $request, int $clientId): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $client  = Client::where('salon_id', $salonId)->findOrFail($clientId);

        $request->validate([
            'reason'    => ['required', 'in:client_request,legal,deceased,other'],
            'confirmed' => ['required', 'accepted'],
        ]);

        DB::transaction(function () use ($client, $request) {
            $anonymRef = 'ERASED-' . Str::upper(Str::random(8));

            // Anonymise PII — retain ID and financial totals for accounting
            $client->update([
                'first_name'         => 'Anonymised',
                'last_name'          => 'Client',
                'email'              => $anonymRef . '@erased.velour',
                'phone'              => null,
                'date_of_birth'      => null,
                'allergies'          => null,
                'medical_notes'      => null,
                'marketing_consent'  => false,
                'sms_consent'        => false,
                'email_consent'      => false,
                'status'             => 'erased',
                'tags'               => [],
                'color'              => '#888888',
                'notes'              => null,
            ]);

            // Nullify PII in related notes and formulas
            $client->notes()->update(['content' => '[Erased under GDPR Art. 17]']);
            $client->formulas()->update([
                'notes'        => null,
                'result_notes' => null,
            ]);

            // Log the erasure for compliance audit
            Log::channel('daily')->info('GDPR Erasure executed', [
                'client_id'   => $client->id,
                'salon_id'    => $client->salon_id,
                'reason'      => $request->reason,
                'executed_by' => $request->user()?->id,
                'timestamp'   => now()->toIso8601String(),
                'reference'   => $anonymRef,
            ]);
        });

        return response()->json([
            'message'   => 'Client data has been anonymised in compliance with GDPR Article 17.',
            'reference' => 'GDPR-' . now()->format('Ymd') . '-' . $client->id,
        ]);
    }

    /* ── GET /salon/gdpr/clients/{client}/export ─────────────────────── */
    /**
     * Export all personal data held about a client (GDPR Article 20 — Data Portability).
     * Returns structured JSON suitable for download.
     */
    public function export(Request $request, int $clientId): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $client  = Client::where('salon_id', $salonId)
            ->with(['notes', 'formulas', 'preferredStaff:id,first_name,last_name'])
            ->findOrFail($clientId);

        $appointments = Appointment::with(['services', 'staff:id,first_name,last_name'])
            ->where('salon_id', $salonId)
            ->where('client_id', $client->id)
            ->orderBy('starts_at', 'desc')
            ->get();

        $transactions = PosTransaction::where('salon_id', $salonId)
            ->where('client_id', $client->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $export = [
            'export_metadata' => [
                'generated_at'  => now()->toIso8601String(),
                'generated_by'  => 'Velour Salon SaaS',
                'gdpr_basis'    => 'Article 20 — Right to Data Portability',
                'salon_id'      => $salonId,
                'client_id'     => $client->id,
            ],
            'personal_data' => [
                'name'               => $client->full_name,
                'email'              => $client->email,
                'phone'              => $client->phone,
                'date_of_birth'      => $client->date_of_birth,
                'source'             => $client->source,
                'created_at'         => $client->created_at,
                'marketing_consent'  => $client->marketing_consent,
                'sms_consent'        => $client->sms_consent,
                'email_consent'      => $client->email_consent,
                'allergies'          => $client->allergies,
                'medical_notes'      => $client->medical_notes,
                'tags'               => $client->tags,
                'is_vip'             => $client->is_vip,
            ],
            'appointment_history' => $appointments->map(fn($a) => [
                'date'     => Carbon::parse($a->starts_at)->format('Y-m-d H:i'),
                'services' => $a->services->pluck('service_name'),
                'staff'    => optional($a->staff)->first_name,
                'status'   => $a->status,
                'price'    => $a->total_price,
            ]),
            'transaction_history' => $transactions->map(fn($t) => [
                'date'           => $t->created_at->format('Y-m-d H:i'),
                'reference'      => $t->reference,
                'total'          => $t->total,
                'payment_method' => $t->payment_method,
                'items'          => $t->items->map(fn($i) => ['name' => $i->name, 'price' => $i->total]),
            ]),
            'service_notes' => $client->notes->map(fn($n) => [
                'type'    => $n->type,
                'content' => $n->content,
                'date'    => $n->created_at->format('Y-m-d'),
            ]),
        ];

        Log::channel('daily')->info('GDPR Data Export generated', [
            'client_id'   => $client->id,
            'salon_id'    => $salonId,
            'executed_by' => $request->user()?->id,
            'timestamp'   => now()->toIso8601String(),
        ]);

        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="velour-data-export-' . $client->id . '.json"');
    }

    /* ── PUT /salon/gdpr/clients/{client}/consent ────────────────────── */
    /**
     * Update a client's marketing consent preferences (GDPR Article 7).
     */
    public function updateConsent(Request $request, int $clientId): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $client  = Client::where('salon_id', $salonId)->findOrFail($clientId);

        $data = $request->validate([
            'marketing_consent' => ['required', 'boolean'],
            'sms_consent'       => ['required', 'boolean'],
            'email_consent'     => ['required', 'boolean'],
        ]);

        $client->update($data);

        Log::channel('daily')->info('GDPR Consent updated', [
            'client_id'   => $client->id,
            'salon_id'    => $salonId,
            'changes'     => $data,
            'executed_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Consent preferences updated.',
            'consent' => [
                'marketing' => $client->marketing_consent,
                'sms'       => $client->sms_consent,
                'email'     => $client->email_consent,
            ],
        ]);
    }

    /* ── GET /salon/gdpr/audit-log ───────────────────────────────────── */
    /**
     * Return GDPR audit log entries for this salon.
     */
    public function auditLog(Request $request): JsonResponse
    {
        // Reads from Laravel's activity log (spatie/laravel-activitylog)
        $logs = \Spatie\Activitylog\Models\Activity::where('properties->salon_id', $request->attributes->get('salon_id'))
            ->whereIn('description', ['created', 'updated', 'deleted'])
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }
}
