<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Resources;

use App\Modules\Operations\Models\Task;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'parent_task_id' => $this->parent_task_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'assignee_employee_id' => $this->assignee_employee_id,
            'assignee_team_id' => $this->assignee_team_id,
            'created_by' => $this->created_by,
            'due_at' => $this->due_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'subtasks_count' => $this->whenCounted('subtasks'),
            'subtasks' => TaskResource::collection($this->whenLoaded('subtasks')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assignee_employee' => new EmployeeResource($this->whenLoaded('assigneeEmployee')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
