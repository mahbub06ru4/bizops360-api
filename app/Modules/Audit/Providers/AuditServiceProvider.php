<?php

declare(strict_types=1);

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Models\Activity;
use App\Modules\Audit\Policies\ActivityPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Activity::class, ActivityPolicy::class);
    }
}
