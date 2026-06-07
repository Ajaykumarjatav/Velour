<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'business_type_id'  => $this->business_type_id,
            'description'       => $this->description,
            'color'             => $this->color,
            'icon'              => $this->icon,
            'sort_order'        => (int) $this->sort_order,
            'business_type'     => $this->whenLoaded('businessType'),
            'services'          => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
