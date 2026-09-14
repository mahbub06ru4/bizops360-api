<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SiteVisit
 */
class SiteVisitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'unit_id' => $this->unit_id,
            'project_id' => $this->project_id,
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'status' => $this->status->value,
            'conducted_by_employee_id' => $this->conducted_by_employee_id,
            'feedback' => $this->feedback,
            'lead' => $this->whenLoaded('lead', fn () => ['id' => $this->lead->id, 'name' => $this->lead->name]),
            'unit' => $this->whenLoaded('unit', fn () => $this->unit === null ? null : UnitSummaryResource::make($this->unit)),
            'project' => $this->whenLoaded('project', fn () => $this->project === null ? null : ['id' => $this->project->id, 'name' => $this->project->name]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
