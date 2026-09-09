<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Providers;

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Industry\Travel\Policies\BookingPolicy;
use App\Modules\Industry\Travel\Policies\TravellerPolicy;
use App\Modules\Industry\Travel\Policies\VisaApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Traveller::class, TravellerPolicy::class);
        Gate::policy(VisaApplication::class, VisaApplicationPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
