<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A lightweight display embed for a unit — id/number plus its parent
 * project's id/name — for contexts (offers, site visits, bookings) that need
 * enough to render a label without the full {@see UnitResource} (media,
 * prices, etc). Requires `unit.building.project` to be eager-loaded.
 *
 * @mixin Unit
 */
class UnitSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'project_id' => $this->building?->project?->id,
            'project_name' => $this->building?->project?->name,
        ];
    }
}
