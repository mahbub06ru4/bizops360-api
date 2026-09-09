<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Modules\CRM\Models\FollowUp;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FollowUp
 */
class FollowUpResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'followupable_type' => class_basename($this->followupable_type),
            'followupable_id' => $this->followupable_id,
            'assigned_employee_id' => $this->assigned_employee_id,
            'created_by' => $this->created_by,
            'type' => $this->type->value,
            'due_at' => $this->due_at->toIso8601String(),
            'status' => $this->status->value,
            'notes' => $this->notes,
            'outcome' => $this->outcome,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'assigned_employee' => new EmployeeResource($this->whenLoaded('assignedEmployee')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
