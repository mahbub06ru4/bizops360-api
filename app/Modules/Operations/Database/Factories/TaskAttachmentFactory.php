<?php

declare(strict_types=1);

namespace App\Modules\Operations\Database\Factories;

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskAttachment;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskAttachment>
 */
class TaskAttachmentFactory extends Factory
{
    protected $model = TaskAttachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::random(10).'.pdf';

        return [
            'tenant_id' => Tenant::factory(),
            'task_id' => Task::factory(),
            'uploaded_by' => null,
            'disk' => 'local',
            'path' => "attachments/{$name}",
            'original_name' => $name,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1000, 400000),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
