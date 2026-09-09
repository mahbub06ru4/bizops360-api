<?php

declare(strict_types=1);

namespace App\Modules\Operations\Database\Factories;

use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Operations\Models\Project;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'department_id' => null,
            'lead_employee_id' => null,
            'created_by' => null,
            'name' => fake()->sentence(3),
            'code' => Str::upper(Str::random(6)),
            'description' => fake()->optional()->sentence(),
            'status' => ProjectStatus::Active,
            'start_date' => null,
            'due_date' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
