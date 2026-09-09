<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Database\Factories;

use App\Modules\Industry\Travel\Domain\TravellerGender;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Traveller>
 */
class TravellerFactory extends Factory
{
    protected $model = Traveller::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => null,
            'created_by' => null,
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(TravellerGender::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'nationality' => 'Bangladeshi',
            'passport_number' => strtoupper(fake()->bothify('??#######')),
            'passport_expiry' => fake()->dateTimeBetween('+1 year', '+8 years')->format('Y-m-d'),
            'passport_issue_country' => 'Bangladesh',
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->address(),
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
