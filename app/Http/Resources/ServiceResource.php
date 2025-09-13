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
            'description' => $this->description,
            'benefits' => $this->benefits,
            'price' => [
                'amount' => (float) $this->price,
                'formatted' => '$' . number_format($this->price, 2)
            ],
            'duration' => [
                'minutes' => $this->duration,
                'formatted' => $this->formatDuration($this->duration)
            ],
            'is_available' => $this->is_available,
            'stylist_qualifications' => $this->stylist_qualifications,
            'image_url' => $this->image_url,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name ?? null,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Include stylist count if relationship is loaded
            'stylist_count' => $this->when($this->relationLoaded('stylists'), function () {
                return $this->stylists->count();
            }),
            
            // Include full stylist details if specifically loaded
            'stylists' => $this->when($this->relationLoaded('stylists'), function () {
                return $this->stylists->map(function ($stylist) {
                    return [
                        'id' => $stylist->id,
                        'name' => $stylist->name,
                        'email' => $stylist->email,
                        'is_active' => $stylist->is_active ?? true,
                    ];
                });
            }),
        ];
    }

    /**
     * Format duration from minutes to readable format
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' hr' . ($hours > 1 ? 's' : '');
        }

        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ' . $remainingMinutes . ' min';
    }
}   