<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\BookingPassenger;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPassenger>
 */
class BookingPassengerFactory extends Factory
{
    protected $model = BookingPassenger::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => Booking::factory(),
            'traveller_id' => Traveller::factory(),
            'ticket_number' => fake()->optional()->numerify('###-##########'),
            'baggage' => fake()->randomElement(['20kg', '30kg', '40kg']),
            'fare_amount' => fake()->randomFloat(2, 15000, 90000),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
