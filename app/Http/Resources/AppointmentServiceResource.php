<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'service_id'       => $this->service_id,
            'service_name'     => $this->service_name,
            'duration_minutes' => $this->duration_minutes,
            'price'            => number_format((float) $this->price, 2, '.', ''),
            'staff_id'         => $this->staff_id,
        ];
    }
}
