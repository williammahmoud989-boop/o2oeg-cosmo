<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'is_active' => (bool) $this->is_active,
            'salon' => new SalonResource($this->whenLoaded('salon')),
        ];
    }
}
