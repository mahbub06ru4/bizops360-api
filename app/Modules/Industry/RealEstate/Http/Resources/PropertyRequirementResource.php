<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyRequirement
 */
class PropertyRequirementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'preferred_locations' => $this->preferred_locations,
            'unit_type' => $this->unit_type,
            'bedrooms_min' => $this->bedrooms_min,
            'purpose' => $this->purpose->value,
            'notes' => $this->notes,
            'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name]),
            'matches' => PropertyMatchResource::collection($this->whenLoaded('matches')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
