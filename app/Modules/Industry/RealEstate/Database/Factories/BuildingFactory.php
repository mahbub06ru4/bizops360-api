<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'name' => 'Tower '.fake()->randomLetter(),
            'total_floors' => fake()->numberBetween(5, 20),
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
