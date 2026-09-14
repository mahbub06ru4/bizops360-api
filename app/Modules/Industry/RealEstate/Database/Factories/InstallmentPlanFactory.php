<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstallmentPlan>
 */
class InstallmentPlanFactory extends Factory
{
    protected $model = InstallmentPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => RealEstateBooking::factory(),
            'down_payment_amount' => fake()->randomFloat(2, 500000, 2000000),
            'installment_count' => 12,
            'frequency' => PaymentPlanFrequency::Monthly,
            'start_date' => now()->toDateString(),
        ];
    }

    public function forBooking(RealEstateBooking $booking): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $booking->tenant_id,
            'booking_id' => $booking->getKey(),
        ]);
    }
}
