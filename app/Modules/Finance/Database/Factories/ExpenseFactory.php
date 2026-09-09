<?php

declare(strict_types=1);

namespace App\Modules\Finance\Database\Factories;

use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Expense;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'employee_id' => null,
            'recorded_by' => null,
            'category' => ExpenseCategory::Office,
            'supplier_name' => null,
            'title' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 20, 3000),
            'spent_on' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional()->bothify('EXP-####'),
            'note' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function category(ExpenseCategory $category): static
    {
        return $this->state(fn (array $attributes): array => ['category' => $category]);
    }

    public function on(string $date): static
    {
        return $this->state(fn (array $attributes): array => ['spent_on' => $date]);
    }
}
