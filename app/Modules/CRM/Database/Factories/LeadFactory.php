<?php

declare(strict_types=1);

namespace App\Modules\CRM\Database\Factories;

use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'owner_employee_id' => null,
            'converted_customer_id' => null,
            'created_by' => null,
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => fake()->randomElement(['website', 'referral', 'cold_call', 'event']),
            'stage' => LeadStage::New,
            'estimated_value' => fake()->optional()->randomFloat(2, 500, 50000),
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function stage(LeadStage $stage): static
    {
        return $this->state(fn (array $attributes): array => ['stage' => $stage]);
    }
}
