<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Render terminates TLS at its edge and forwards plain HTTP internally.
        // Belt-and-suspenders on top of trustProxies(): force https:// in every
        // generated URL (signed document/attachment links included) regardless
        // of how the request actually arrived.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // dedoc/scramble restricts /docs/api to the `local` env unless this
        // gate allows it. The rendered doc is just endpoint/parameter shapes
        // (no tenant data, no secrets), so leave it open in production —
        // it's how the Flutter team and integrators reference the contract.
        // Gate::allows() always passes the (possibly null, for a guest)
        // user as the first argument — a zero-arg closure here silently
        // evaluates to false instead of true, so it must be declared.
        Gate::define('viewApiDocs', fn (?User $user): bool => true);
    }
}
