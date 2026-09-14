<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Domain\ProjectType;
use App\Modules\Industry\RealEstate\Domain\RequirementPurpose;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyRequirement>
 */
class PropertyRequirementFactory extends Factory
{
    protected $model = PropertyRequirement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->randomFloat(2, 5000000, 9000000);

        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'budget_min' => $min,
            'budget_max' => $min + fake()->randomFloat(2, 1000000, 5000000),
            'preferred_locations' => fake()->randomElement(['Uttara', 'Bashundhara', 'Dhanmondi', 'Gulshan']),
            'unit_type' => fake()->randomElement(ProjectType::values()),
            'bedrooms_min' => fake()->numberBetween(2, 4),
            'purpose' => fake()->randomElement(RequirementPurpose::cases()),
            'notes' => null,
        ];
    }

    public function forLead(Lead $lead): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->getKey(),
        ]);
    }
}
