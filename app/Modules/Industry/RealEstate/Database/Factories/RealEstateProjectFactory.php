<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Domain\ProjectType;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RealEstateProject>
 */
class RealEstateProjectFactory extends Factory
{
    protected $model = RealEstateProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Uttara Diabari Heights', 'Bashundhara Residency', 'Purbachal Green View', 'Chattogram Hillside Towers']).' '.fake()->numberBetween(1, 99);

        return [
            'tenant_id' => Tenant::factory(),
            'created_by' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'project_type' => ProjectType::Apartment,
            'description' => fake()->paragraph(),
            'status' => ProjectStatus::Draft,
            'total_land_area' => fake()->randomFloat(2, 5, 50),
            'currency' => 'BDT',
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function type(ProjectType $type): static
    {
        return $this->state(fn (array $attributes): array => ['project_type' => $type]);
    }

    public function status(ProjectStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
