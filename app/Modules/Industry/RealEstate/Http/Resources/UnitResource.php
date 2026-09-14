<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Unit
 */
class UnitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'unit_number' => $this->unit_number,
            'floor' => $this->floor,
            'size_sqft' => $this->size_sqft,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'facing' => $this->facing?->value,
            'parking_spaces' => $this->parking_spaces,
            'status' => $this->status->value,
            'media' => UnitMediaResource::collection($this->whenLoaded('media')),
            'prices' => UnitPriceResource::collection($this->whenLoaded('prices')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
