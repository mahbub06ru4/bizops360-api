<?php

declare(strict_types=1);

namespace App\Modules\HR\Database\Factories;

use App\Modules\HR\Models\LeaveType;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Annual', 'Sick', 'Casual', 'Unpaid']).' '.Str::random(3),
            'code' => Str::upper(Str::random(6)),
            'default_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes): array => ['is_paid' => false]);
    }

    public function autoApproved(): static
    {
        return $this->state(fn (array $attributes): array => ['requires_approval' => false]);
    }
}
