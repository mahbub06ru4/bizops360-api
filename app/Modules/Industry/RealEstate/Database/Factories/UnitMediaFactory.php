<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\UnitMediaType;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Industry\RealEstate\Models\UnitMedia;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitMedia>
 */
class UnitMediaFactory extends Factory
{
    protected $model = UnitMedia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'unit_id' => Unit::factory(),
            'file_path' => 'demo/unit-media/'.fake()->uuid().'.jpg',
            'media_type' => UnitMediaType::Image,
            'sort_order' => 0,
        ];
    }

    public function forUnit(Unit $unit): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $unit->tenant_id,
            'unit_id' => $unit->getKey(),
        ]);
    }
}
