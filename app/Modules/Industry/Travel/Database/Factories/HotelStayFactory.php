<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Domain\BoardBasis;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\HotelStay;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HotelStay>
 */
class HotelStayFactory extends Factory
{
    protected $model = HotelStay::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+1 week', '+2 months');
        $nights = fake()->numberBetween(2, 6);

        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => Booking::factory(),
            'hotel_name' => fake()->company().' Hotel',
            'city' => fake()->randomElement(['Bangkok', 'Kuala Lumpur', 'Makkah', 'Madinah', 'Dubai']),
            'country' => fake()->randomElement(['Thailand', 'Malaysia', 'Saudi Arabia', 'UAE']),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => (clone $checkIn)->modify("+{$nights} days")->format('Y-m-d'),
            'nights' => $nights,
            'room_type' => fake()->randomElement(['Twin', 'Triple', 'Quad']),
            'rooms' => fake()->numberBetween(1, 3),
            'guests' => fake()->numberBetween(1, 6),
            'board_basis' => fake()->randomElement(BoardBasis::cases()),
            'confirmation_no' => strtoupper(fake()->bothify('HTL-#####')),
            'note' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
