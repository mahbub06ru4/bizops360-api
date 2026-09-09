<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\BookingType;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 20000, 150000);

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => null,
            'handled_by_employee_id' => null,
            'invoice_id' => null,
            'created_by' => null,
            'reference' => 'BKG-'.fake()->unique()->numerify('######'),
            'type' => BookingType::AirTicket,
            'title' => 'DAC–BKK return, Biman',
            'supplier_name' => fake()->company(),
            'pnr' => strtoupper(fake()->bothify('??####')),
            'airline' => 'Biman Bangladesh',
            'origin' => 'DAC',
            'destination' => 'BKK',
            'depart_on' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'return_on' => fake()->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
            'status' => BookingStatus::Quoted,
            'cost_amount' => $cost,
            'sell_amount' => $cost + fake()->randomFloat(2, 2000, 12000),
            'commission_amount' => fake()->randomFloat(2, 500, 4000),
            'refund_amount' => 0,
            'currency' => 'BDT',
            'issued_on' => null,
            'cancelled_on' => null,
            'refund_on' => null,
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function type(BookingType $type): static
    {
        return $this->state(fn (array $attributes): array => ['type' => $type]);
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
