<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\InstallmentStatus;
use App\Modules\Industry\RealEstate\Models\Installment;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installment>
 */
class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'installment_plan_id' => InstallmentPlan::factory(),
            'sequence' => 1,
            'due_date' => now()->addMonth()->toDateString(),
            'amount' => fake()->randomFloat(2, 100000, 900000),
            'status' => InstallmentStatus::Pending,
            'invoice_id' => null,
        ];
    }

    public function forPlan(InstallmentPlan $plan): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $plan->tenant_id,
            'installment_plan_id' => $plan->getKey(),
        ]);
    }
}
