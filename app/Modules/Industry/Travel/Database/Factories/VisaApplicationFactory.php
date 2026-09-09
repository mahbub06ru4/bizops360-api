<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisaApplication>
 */
class VisaApplicationFactory extends Factory
{
    protected $model = VisaApplication::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'traveller_id' => Traveller::factory(),
            'customer_id' => null,
            'assigned_employee_id' => null,
            'created_by' => null,
            'destination_country' => fake()->randomElement(['Thailand', 'Malaysia', 'India', 'UAE', 'Saudi Arabia']),
            'visa_type' => fake()->randomElement(['tourist', 'business', 'umrah', 'student']),
            'mission' => fake()->optional()->company(),
            'stage' => VisaStage::Draft,
            'reference_no' => strtoupper(fake()->bothify('VA-####')),
            'application_no' => null,
            'government_fee' => fake()->randomFloat(2, 2000, 15000),
            'service_charge' => fake()->randomFloat(2, 1000, 5000),
            'submitted_on' => null,
            'decision_on' => null,
            'decision_note' => null,
            'expected_travel_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function stage(VisaStage $stage): static
    {
        return $this->state(fn (array $attributes): array => ['stage' => $stage]);
    }

    public function forTraveller(Traveller $traveller): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $traveller->tenant_id,
            'traveller_id' => $traveller->getKey(),
        ]);
    }
}
