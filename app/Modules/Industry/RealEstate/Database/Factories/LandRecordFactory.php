<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandRecord>
 */
class LandRecordFactory extends Factory
{
    protected $model = LandRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'mouza' => fake()->randomElement(['Diabari', 'Bawnia', 'Baunia', 'Kanchan']),
            'jl_no' => (string) fake()->numberBetween(1, 200),
            'khatian_no' => 'BS-'.fake()->numberBetween(1000, 9999),
            'dag_no' => (string) fake()->numberBetween(100, 999),
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
