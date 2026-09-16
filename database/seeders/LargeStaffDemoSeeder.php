<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Roles;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Designation;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * Demo-only: bulk-generates 50 staff employees for the Wanderlust tenant,
 * spread across its existing departments/designations, with a handful linked
 * to login users on non-owner roles — enough volume to exercise pagination
 * and search on the admin panel's Employees screen. Not part of the regular
 * DemoSeeder pipeline; run explicitly with:
 *
 *   ./vendor/bin/sail artisan db:seed --class=Database\\Seeders\\LargeStaffDemoSeeder
 */
class LargeStaffDemoSeeder extends Seeder
{
    private const int COUNT = 50;

    private const int LINKED_USER_COUNT = 12;

    public function run(TenantContext $context, PermissionRegistrar $registrar): void
    {
        $tenant = Tenant::query()->where('slug', 'wanderlust')->firstOrFail();

        $context->set($tenant);
        $registrar->setPermissionsTeamId($tenant->getKey());

        $departments = Department::query()->where('tenant_id', $tenant->getKey())->get();

        if ($departments->isEmpty()) {
            $this->command->error('Wanderlust has no departments yet — run the main DemoSeeder first.');

            return;
        }

        $designationsByDepartment = Designation::query()
            ->where('tenant_id', $tenant->getKey())
            ->get()
            ->groupBy('department_id');

        $linkableRoles = [Roles::ADMIN, Roles::MANAGER, Roles::STAFF];
        $statuses = [
            EmploymentStatus::Active, EmploymentStatus::Active, EmploymentStatus::Active,
            EmploymentStatus::Probation, EmploymentStatus::OnLeave,
        ];

        for ($i = 1; $i <= self::COUNT; $i++) {
            $department = $departments->random();
            $designations = $designationsByDepartment->get($department->getKey());

            $employee = Employee::factory()->forTenant($tenant)->create([
                'department_id' => $department->getKey(),
                'designation_id' => $designations?->isNotEmpty() === true ? $designations->random()->getKey() : null,
                'employee_code' => sprintf('EMP-BULK-%03d', $i),
                'employment_status' => fake()->randomElement($statuses),
            ]);

            if ($i <= self::LINKED_USER_COUNT) {
                $role = $linkableRoles[($i - 1) % count($linkableRoles)];
                $user = User::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'name' => $employee->full_name,
                    'email' => "bulk-staff-{$i}@wanderlust.test",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $user->assignRole($role);

                $employee->update(['user_id' => $user->getKey()]);
            }
        }

        $this->command->info(sprintf(
            'Created %d employees for Wanderlust (%d linked to login users across %s).',
            self::COUNT,
            self::LINKED_USER_COUNT,
            implode('/', $linkableRoles),
        ));
    }
}
