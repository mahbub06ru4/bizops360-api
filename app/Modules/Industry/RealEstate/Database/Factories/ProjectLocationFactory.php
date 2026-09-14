<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Models\ProjectLocation;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectLocation>
 */
class ProjectLocationFactory extends Factory
{
    protected $model = ProjectLocation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'area' => fake()->randomElement(['Uttara', 'Bashundhara', 'Purbachal', 'Mirpur', 'Dhanmondi']),
            'sector' => fake()->numberBetween(1, 18).'',
            'road' => 'Road '.fake()->numberBetween(1, 20),
            'landmark' => 'Near '.fake()->randomElement(['Diabari Metro Station', 'Jamuna Future Park', 'Hazrat Shahjalal Airport', '300 Feet Road']),
            'latitude' => fake()->latitude(23.6, 23.9),
            'longitude' => fake()->longitude(90.3, 90.5),
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
