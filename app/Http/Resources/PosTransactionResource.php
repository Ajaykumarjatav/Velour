<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'reference'         => $this->reference,
            'status'            => $this->status,
            'subtotal'          => number_format((float) $this->subtotal, 2, '.', ''),
            'discount_amount'   => number_format((float) $this->discount_amount, 2, '.', ''),
            'tax_amount'        => number_format((float) $this->tax_amount, 2, '.', ''),
            'tip_amount'        => number_format((float) $this->tip_amount, 2, '.', ''),
            'total'             => number_format((float) $this->total, 2, '.', ''),
            'payment_method'    => $this->payment_method,
            'completed_at'      => $this->completed_at?->toIso8601String(),
            'created_at'        => $this->created_at?->toIso8601String(),
            'client'            => new ClientResource($this->whenLoaded('client')),
            'staff'             => new StaffResource($this->whenLoaded('staff')),
            'items'             => PosTransactionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
