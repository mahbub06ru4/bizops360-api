<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Models\LandShare;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandShare>
 */
class LandShareFactory extends Factory
{
    protected $model = LandShare::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'total_shares' => fake()->randomElement([100, 200, 400]),
            'share_value' => fake()->randomFloat(2, 50000, 500000),
        ];
    }

    public function forProject(RealEstateProject $project): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->getKey(),
        ]);
    }
}
