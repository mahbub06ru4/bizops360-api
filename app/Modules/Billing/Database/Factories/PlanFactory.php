<?php

declare(strict_types=1);

namespace App\Modules\Billing\Database\Factories;

use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'price_amount' => fake()->randomElement(['0.00', '29.00', '79.00', '199.00']),
            'billing_interval' => 'month',
            'features' => [],
            'is_active' => true,
        ];
    }
}
