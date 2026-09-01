<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'full_name'  => $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'color'      => $this->color,
            'initials'   => strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1)),
            'avatar'     => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'is_vip'     => (bool) $this->is_vip,
            'status'     => $this->status,
            'tags'       => $this->tags ?? [],
            'visit_count'=> (int) $this->visit_count,
            'total_spent'=> number_format((float) $this->total_spent, 2, '.', ''),
            'last_visit_at' => $this->last_visit_at?->toDateString(),
            'date_of_birth' => $this->date_of_birth?->toDateString(),

            // Sensitive — only for authenticated staff
            'allergies'         => $this->when($request->user() !== null, $this->allergies),
            'medical_notes'     => $this->when($request->user() !== null, $this->medical_notes),
            'marketing_consent' => $this->when($request->user() !== null, (bool) $this->marketing_consent),

            'created_at'        => $this->created_at?->toDateString(),

            // Relations
            'preferred_staff' => new StaffResource($this->whenLoaded('preferredStaff')),
            'notes'           => ClientNoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}
