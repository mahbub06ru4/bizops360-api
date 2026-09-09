<?php

declare(strict_types=1);

namespace App\Modules\Operations\Models;

use App\Models\User;
use App\Modules\Operations\Database\Factories\TaskFactory;
use App\Modules\Operations\Domain\TaskPriority;
use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A unit of work for a tenant. May belong to a project, may be a subtask of
 * another task, and may be assigned to an employee and/or a team.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $project_id
 * @property int|null $parent_task_id
 * @property int|null $assignee_employee_id
 * @property int|null $assignee_team_id
 * @property int|null $created_by
 * @property string $title
 * @property string|null $description
 * @property TaskStatus $status
 * @property TaskPriority $priority
 * @property Carbon|null $due_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'parent_task_id', 'title', 'description', 'priority', 'due_at'])]
class Task extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Task, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /** @return HasMany<Task, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function assigneeEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assignee_employee_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function assigneeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assignee_team_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null && $this->status->isOpen() && $this->due_at->isPast();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }
}
