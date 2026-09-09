<?php

declare(strict_types=1);

namespace App\Modules\HR\Providers;

use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\AttendanceSetting;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\HR\Models\Holiday;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveType;
use App\Modules\HR\Policies\AttendancePolicy;
use App\Modules\HR\Policies\AttendanceSettingPolicy;
use App\Modules\HR\Policies\EmployeeDocumentPolicy;
use App\Modules\HR\Policies\HolidayPolicy;
use App\Modules\HR\Policies\LeaveBalancePolicy;
use App\Modules\HR\Policies\LeaveRequestPolicy;
use App\Modules\HR\Policies\LeaveTypePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Holiday::class, HolidayPolicy::class);
        Gate::policy(LeaveType::class, LeaveTypePolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(LeaveBalance::class, LeaveBalancePolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(AttendanceSetting::class, AttendanceSettingPolicy::class);
        Gate::policy(EmployeeDocument::class, EmployeeDocumentPolicy::class);
    }
}
