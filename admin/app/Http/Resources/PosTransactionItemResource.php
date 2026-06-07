<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosTransactionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type,
            'quantity'   => (int) $this->quantity,
            'unit_price' => number_format((float) $this->unit_price, 2, '.', ''),
            'discount'   => number_format((float) $this->discount, 2, '.', ''),
            'total'      => number_format((float) $this->total, 2, '.', ''),
            'staff'      => new StaffResource($this->whenLoaded('staff')),
        ];
    }
}
