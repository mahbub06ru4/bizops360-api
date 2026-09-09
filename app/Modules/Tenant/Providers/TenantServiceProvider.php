<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Providers;

use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Policies\CompanyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Tenant::class, CompanyPolicy::class);

        // Reset the bound tenant between queue jobs / octane requests.
        $this->app->terminating(function (): void {
            $this->app->make(TenantContext::class)->clear();
            $this->app->make(PermissionRegistrar::class)->setPermissionsTeamId(null);
        });
    }
}
