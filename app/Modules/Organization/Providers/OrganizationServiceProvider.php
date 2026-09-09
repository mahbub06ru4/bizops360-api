<?php

declare(strict_types=1);

namespace App\Modules\Organization\Providers;

use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Organization\Policies\BranchPolicy;
use App\Modules\Organization\Policies\DepartmentPolicy;
use App\Modules\Organization\Policies\DesignationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OrganizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Branch::class, BranchPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Designation::class, DesignationPolicy::class);
    }
}
