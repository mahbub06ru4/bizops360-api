<?php

declare(strict_types=1);

namespace App\Providers;

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
    }
}
