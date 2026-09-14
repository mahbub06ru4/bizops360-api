<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\PropertyMatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PropertyMatch
 */
class PropertyMatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requirement_id' => $this->requirement_id,
            'unit_id' => $this->unit_id,
            'match_score' => $this->match_score,
            'status' => $this->status->value,
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'project' => $this->whenLoaded('unit', fn () => $this->unit->building?->project === null ? null : [
                'id' => $this->unit->building->project->id,
                'name' => $this->unit->building->project->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
