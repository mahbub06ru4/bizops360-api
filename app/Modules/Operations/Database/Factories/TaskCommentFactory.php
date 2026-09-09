<?php

declare(strict_types=1);

namespace App\Modules\Operations\Database\Factories;

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskComment;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskComment>
 */
class TaskCommentFactory extends Factory
{
    protected $model = TaskComment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'task_id' => Task::factory(),
            'author_id' => null,
            'body' => fake()->paragraph(),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
