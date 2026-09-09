<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Resources;

use App\Modules\Operations\Models\Project;
use App\Modules\Organization\Http\Resources\DepartmentResource;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status->value,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'department_id' => $this->department_id,
            'lead_employee_id' => $this->lead_employee_id,
            'created_by' => $this->created_by,
            'tasks_count' => $this->whenCounted('tasks'),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'lead' => new EmployeeResource($this->whenLoaded('lead')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
