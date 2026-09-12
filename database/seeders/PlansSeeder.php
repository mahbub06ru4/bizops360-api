<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'starter', 'name' => 'Starter', 'price_amount' => '0.00', 'features' => ['seats' => 5]],
            ['code' => 'growth', 'name' => 'Growth', 'price_amount' => '29.00', 'features' => ['seats' => 25]],
            ['code' => 'scale', 'name' => 'Scale', 'price_amount' => '79.00', 'features' => ['seats' => null]],
        ] as $spec) {
            Plan::updateOrCreate(
                ['code' => $spec['code']],
                [
                    'name' => $spec['name'],
                    'price_amount' => $spec['price_amount'],
                    'billing_interval' => 'month',
                    'features' => $spec['features'],
                    'is_active' => true,
                ],
            );
        }
    }
}
