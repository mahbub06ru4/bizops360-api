<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RealEstateBooking>
 */
class RealEstateBookingFactory extends Factory
{
    protected $model = RealEstateBooking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'unit_id' => Unit::factory(),
            'accepted_offer_id' => Offer::factory(),
            'customer_id' => null,
            'agreed_price' => fake()->randomFloat(2, 5000000, 15000000),
            'status' => BookingStatus::Reserved,
            'booked_at' => null,
        ];
    }
}
