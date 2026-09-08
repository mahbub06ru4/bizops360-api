<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Providers;

use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Reset the bound tenant between queue jobs / octane requests.
        $this->app->terminating(function (): void {
            $this->app->make(TenantContext::class)->clear();
        });
    }
}
