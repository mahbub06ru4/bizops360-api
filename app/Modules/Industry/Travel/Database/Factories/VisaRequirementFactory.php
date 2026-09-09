<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Industry\Travel\Models\VisaRequirement;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisaRequirement>
 */
class VisaRequirementFactory extends Factory
{
    protected $model = VisaRequirement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'visa_application_id' => VisaApplication::factory(),
            'name' => fake()->randomElement(['Passport', 'Photograph', 'Bank statement', 'NOC', 'Trade licence', 'Air ticket']),
            'is_mandatory' => true,
            'collected' => false,
            'collected_on' => null,
            'note' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function forApplication(VisaApplication $application): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $application->tenant_id,
            'visa_application_id' => $application->getKey(),
        ]);
    }

    public function collected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'collected' => true,
            'collected_on' => now()->toDateString(),
        ]);
    }
}
