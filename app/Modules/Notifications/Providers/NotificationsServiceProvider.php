<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        // Sanctum bearer-token auth for the private-channel handshake, same
        // guard as every other /api/v1 route.
        Broadcast::routes(['prefix' => 'api/v1', 'middleware' => ['auth:sanctum']]);

        require base_path('routes/channels.php');
    }
}
