<?php

declare(strict_types=1);

namespace App\Modules\AdminUi\Providers;

use Illuminate\Support\ServiceProvider;

class AdminUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
