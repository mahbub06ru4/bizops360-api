<?php

declare(strict_types=1);

namespace App\Modules\Operations\Database\Factories;

use App\Modules\Operations\Domain\TaskPriority;
use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Operations\Models\Task;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => null,
            'parent_task_id' => null,
            'assignee_employee_id' => null,
            'assignee_team_id' => null,
            'created_by' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Normal,
            'due_at' => null,
            'completed_at' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function status(TaskStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
