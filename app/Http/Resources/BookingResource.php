<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_code' => $this->booking_code,
            'booking_date' => $this->booking_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_price' => $this->total_price,
            'deposit_amount' => $this->deposit_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_id' => $this->payment_id,
            'payment_method' => $this->payment_method,
            'payment_receipt' => $this->payment_receipt ? asset('storage/' . $this->payment_receipt) : null,
            'notes' => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'requires_deposit' => $this->salon ? $this->salon->requires_deposit : false,
            'customer' => new UserResource($this->whenLoaded('user')),
            'salon' => new SalonResource($this->whenLoaded('salon')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'review' => $this->review ? [
                'id' => $this->review->id,
                'rating' => $this->review->rating,
                'comment' => $this->review->comment,
            ] : null,
        ];
    }
}
