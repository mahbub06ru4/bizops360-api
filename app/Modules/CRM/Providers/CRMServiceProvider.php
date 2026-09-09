<?php

declare(strict_types=1);

namespace App\Modules\CRM\Providers;

use App\Modules\CRM\Console\Commands\SendFollowUpRemindersCommand;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Observers\CustomerObserver;
use App\Modules\CRM\Observers\LeadObserver;
use App\Modules\CRM\Policies\CustomerPolicy;
use App\Modules\CRM\Policies\FollowUpPolicy;
use App\Modules\CRM\Policies\LeadPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CRMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(FollowUp::class, FollowUpPolicy::class);

        Lead::observe(LeadObserver::class);
        Customer::observe(CustomerObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SendFollowUpRemindersCommand::class]);
        }
    }
}
