<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'full_name'      => $this->first_name . ' ' . $this->last_name,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'initials'       => $this->initials,
            'color'          => $this->color,
            'role'           => $this->role,
            'specialisms'    => $this->specialisms ?? [],
            'bio'            => $this->bio,
            'avatar'         => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'is_active'      => (bool) $this->is_active,
            'bookable_online'=> (bool) $this->bookable_online,
            'access_level'   => $this->access_level,
            'working_days'   => $this->working_days ?? [],
            'start_time'     => $this->start_time,
            'end_time'       => $this->end_time,

            // Sensitive — only for managers+
            'commission_rate'=> $this->when(
                in_array($request->attributes->get('access_level'), ['owner', 'manager']),
                $this->commission_rate
            ),
            'email' => $this->when(
                in_array($request->attributes->get('access_level'), ['owner', 'manager']),
                $this->email
            ),
        ];
    }
}
