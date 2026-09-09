<?php

declare(strict_types=1);

namespace App\Modules\Organization\Providers;

use App\Models\User;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;
use App\Modules\Organization\Policies\BranchPolicy;
use App\Modules\Organization\Policies\DepartmentPolicy;
use App\Modules\Organization\Policies\DesignationPolicy;
use App\Modules\Organization\Policies\EmployeePolicy;
use App\Modules\Organization\Policies\TeamPolicy;
use App\Modules\Organization\Policies\UserPolicy;
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
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
