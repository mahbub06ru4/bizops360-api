<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\UnitPriceType;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Industry\RealEstate\Models\UnitPrice;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitPrice>
 */
class UnitPriceFactory extends Factory
{
    protected $model = UnitPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'unit_id' => Unit::factory(),
            'price' => fake()->randomFloat(2, 6000000, 18000000),
            'price_type' => UnitPriceType::Base,
            'effective_from' => now()->toDateString(),
        ];
    }

    public function forUnit(Unit $unit): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $unit->tenant_id,
            'unit_id' => $unit->getKey(),
        ]);
    }

    public function type(UnitPriceType $type): static
    {
        return $this->state(fn (array $attributes): array => ['price_type' => $type]);
    }
}
