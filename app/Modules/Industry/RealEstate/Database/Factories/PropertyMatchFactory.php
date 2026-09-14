<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\PropertyMatchStatus;
use App\Modules\Industry\RealEstate\Models\PropertyMatch;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyMatch>
 */
class PropertyMatchFactory extends Factory
{
    protected $model = PropertyMatch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'requirement_id' => PropertyRequirement::factory(),
            'unit_id' => Unit::factory(),
            'match_score' => fake()->randomFloat(2, 0, 100),
            'status' => PropertyMatchStatus::Suggested,
        ];
    }

    public function forRequirement(PropertyRequirement $requirement): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $requirement->tenant_id,
            'requirement_id' => $requirement->getKey(),
        ]);
    }
}
