<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\UnitFacing;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $floor = fake()->numberBetween(1, 15);

        return [
            'tenant_id' => Tenant::factory(),
            'building_id' => Building::factory(),
            'unit_number' => 'U'.$floor.'-'.fake()->numberBetween(1, 4),
            'floor' => $floor,
            'size_sqft' => fake()->randomFloat(2, 850, 2400),
            'bedrooms' => fake()->numberBetween(2, 4),
            'bathrooms' => fake()->numberBetween(2, 3),
            'facing' => fake()->randomElement(UnitFacing::cases()),
            'parking_spaces' => fake()->numberBetween(0, 2),
            'status' => UnitStatus::Available,
        ];
    }

    public function forBuilding(Building $building): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $building->tenant_id,
            'building_id' => $building->getKey(),
        ]);
    }

    public function status(UnitStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
