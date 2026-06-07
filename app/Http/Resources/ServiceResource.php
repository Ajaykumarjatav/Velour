<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'image'                 => $this->image_url,
            'duration_minutes'      => (int) $this->duration_minutes,
            'buffer_minutes'        => (int) $this->buffer_minutes,
            'price'                 => number_format((float) $this->price, 2, '.', ''),
            'price_from'            => $this->price_from ? number_format((float) $this->price_from, 2, '.', '') : null,
            'price_on_consultation' => (bool) $this->price_on_consultation,
            'deposit_type'          => $this->deposit_type,
            'deposit_value'         => $this->deposit_value ? number_format((float) $this->deposit_value, 2, '.', '') : null,
            'online_bookable'       => (bool) $this->online_bookable,
            'show_in_menu'          => (bool) $this->show_in_menu,
            'status'                => $this->status,
            'sort_order'                => (int) $this->sort_order,
            'color'                     => $this->color,
            'variants'                  => $this->normalizedVariants(),
            'addons'                    => $this->normalizedAddons(),
            'dynamic_pricing_enabled'   => (bool) $this->dynamic_pricing_enabled,
            'staff_level'               => $this->staff_level,
            'category'                  => new ServiceCategoryResource($this->whenLoaded('category')),
            'staff'                     => StaffResource::collection($this->whenLoaded('staff')),
        ];
    }
}
