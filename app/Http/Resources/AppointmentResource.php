<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'reference'       => $this->reference,
            'status'          => $this->status,
            'starts_at'       => $this->starts_at?->toIso8601String(),
            'ends_at'         => $this->ends_at?->toIso8601String(),
            'duration_minutes'=> $this->duration_minutes,
            'total_price'     => number_format((float) $this->total_price, 2, '.', ''),
            'deposit_paid'    => (bool) $this->deposit_paid,
            'source'          => $this->source,
            'client_notes'    => $this->client_notes,
            'internal_notes'  => $this->when(
                $request->user()?->staffProfile?->access_level !== 'staff',
                $this->internal_notes
            ),
            'reminder_sent'   => (bool) $this->reminder_sent,
            'created_at'      => $this->created_at?->toIso8601String(),

            // Relations — only included when loaded
            'client'  => new ClientResource($this->whenLoaded('client')),
            'staff'   => new StaffResource($this->whenLoaded('staff')),
            'services'=> AppointmentServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
