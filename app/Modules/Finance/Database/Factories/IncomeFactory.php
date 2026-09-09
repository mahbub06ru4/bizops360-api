<?php

declare(strict_types=1);

namespace App\Modules\Finance\Database\Factories;

use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Income;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Income>
 */
class IncomeFactory extends Factory
{
    protected $model = Income::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => null,
            'recorded_by' => null,
            'category' => IncomeCategory::Other,
            'source' => fake()->optional()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'received_on' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional()->bothify('RCPT-####'),
            'note' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function category(IncomeCategory $category): static
    {
        return $this->state(fn (array $attributes): array => ['category' => $category]);
    }

    public function on(string $date): static
    {
        return $this->state(fn (array $attributes): array => ['received_on' => $date]);
    }
}
