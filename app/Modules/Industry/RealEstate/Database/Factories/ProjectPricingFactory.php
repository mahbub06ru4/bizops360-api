<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Models\ProjectPricing;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectPricing>
 */
class ProjectPricingFactory extends Factory
{
    protected $model = ProjectPricing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $land = fake()->randomFloat(2, 20000000, 80000000);
        $construction = fake()->randomFloat(2, 30000000, 120000000);
        $consultancy = fake()->randomFloat(2, 1000000, 5000000);

        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'land_cost' => $land,
            'construction_cost' => $construction,
            'consultancy_cost' => $consultancy,
            'estimated_total' => $land + $construction + $consultancy,
            'currency' => 'BDT',
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
