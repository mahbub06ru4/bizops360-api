<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\PackageItineraryItem;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageItineraryItem>
 */
class PackageItineraryItemFactory extends Factory
{
    protected $model = PackageItineraryItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'booking_id' => Booking::factory(),
            'day_number' => fake()->numberBetween(1, 6),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'city' => fake()->optional()->city(),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
