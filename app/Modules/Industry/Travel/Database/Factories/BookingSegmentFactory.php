<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\BookingSegment;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingSegment>
 */
class BookingSegmentFactory extends Factory
{
    protected $model = BookingSegment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $depart = fake()->dateTimeBetween('+1 week', '+2 months');

        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => Booking::factory(),
            'sequence' => 1,
            'flight_number' => 'BG'.fake()->numerify('###'),
            'airline' => 'Biman Bangladesh',
            'from_airport' => 'DAC',
            'to_airport' => 'BKK',
            'depart_at' => $depart->format('Y-m-d H:i:s'),
            'arrive_at' => (clone $depart)->modify('+3 hours')->format('Y-m-d H:i:s'),
            'cabin' => 'Y',
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
