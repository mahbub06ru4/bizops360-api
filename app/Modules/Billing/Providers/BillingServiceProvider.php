<?php

declare(strict_types=1);

namespace App\Modules\Billing\Providers;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Subscription::class, SubscriptionPolicy::class);
    }
}
