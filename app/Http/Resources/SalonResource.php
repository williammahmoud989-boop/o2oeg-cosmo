<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalonResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'address' => $this->address,
            'city' => $this->city,
            'governorate' => $this->governorate,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'rating' => (float) ($this->rating ?? 4.8),
            'total_reviews' => (int) ($this->total_reviews ?? 0),
            'is_featured' => (bool) $this->is_featured,
            'requires_deposit' => (bool) $this->requires_deposit,
            'deposit_percentage' => $this->deposit_percentage ?? 0,
            'deposit_days' => $this->deposit_days ?? [],
            'whatsapp_number' => $this->whatsapp_number,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'tiktok_url' => $this->tiktok_url,
            'gallery' => $this->gallery ?? [],
            'payment_methods' => $this->payment_methods ?? [],
            'vodafone_cash_number' => $this->vodafone_cash_number,
            'instapay_id' => $this->instapay_id,
            'is_active' => (bool) ($this->status === 'active'),
            'created_at' => $this->created_at,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
