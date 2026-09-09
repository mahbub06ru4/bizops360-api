<?php

declare(strict_types=1);

namespace App\Modules\Organization\Database\Factories;

use App\Modules\Organization\Models\Team;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_employee_id' => null,
            'name' => fake()->randomElement(['Alpha', 'Bravo', 'Charlie', 'Delta']).' '.Str::random(3),
            'description' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
