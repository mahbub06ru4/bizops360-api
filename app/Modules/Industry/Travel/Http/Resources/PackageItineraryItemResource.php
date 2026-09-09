<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\PackageItineraryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackageItineraryItem
 */
class PackageItineraryItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'day_number' => $this->day_number,
            'title' => $this->title,
            'description' => $this->description,
            'city' => $this->city,
        ];
    }
}
