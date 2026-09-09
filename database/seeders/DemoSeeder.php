<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use App\Modules\Organization\Actions\CreateBranch;
use App\Modules\Organization\Actions\CreateDepartment;
use App\Modules\Organization\Actions\CreateDesignation;
use App\Modules\Organization\Actions\CreateEmployee;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Data\EmployeeData;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Two demo tenants, each with one user per role. Passwords are all "password".
 * Emails: {role}@{slug}.test
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $rbac = app(ProvisionTenantRbac::class);
        $registrar = app(PermissionRegistrar::class);
        $context = app(TenantContext::class);

        foreach ([
            ['name' => 'Wanderlust Travel', 'slug' => 'wanderlust', 'industry' => 'travel'],
            ['name' => 'Skyline Properties', 'slug' => 'skyline', 'industry' => 'real_estate'],
        ] as $spec) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => $spec['slug']],
                ['name' => $spec['name'], 'industry' => $spec['industry']],
            );

            $rbac->handle($tenant);

            $context->set($tenant);
            $registrar->setPermissionsTeamId($tenant->getKey());

            foreach (Roles::all() as $role) {
                $user = User::firstOrNew(['email' => "{$role}@{$spec['slug']}.test"]);
                $user->tenant_id = $tenant->getKey();
                $user->name = Str::headline($role).' '.Str::headline($spec['slug']);
                $user->password = Hash::make('password');
                $user->email_verified_at = now();
                $user->save();

                if (! $user->hasRole($role)) {
                    $user->assignRole($role);
                }
            }

            if (Branch::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                app(CreateBranch::class)->handle(new BranchData(
                    name: 'Head Office',
                    code: 'HO',
                    address: null,
                    phone: null,
                    email: null,
                    isHeadOffice: true,
                ));

                foreach (['Sales', 'Operations', 'Finance'] as $i => $name) {
                    $department = app(CreateDepartment::class)->handle(new DepartmentData(
                        branchId: null,
                        name: $name,
                        code: strtoupper(substr($name, 0, 3)),
                        description: null,
                    ));

                    app(CreateDesignation::class)->handle(new DesignationData(
                        departmentId: $department->getKey(),
                        title: $name.' Manager',
                        rank: $i + 1,
                    ));
                }
            }

            if (Employee::query()->where('tenant_id', $tenant->getKey())->doesntExist()) {
                $staff = User::where('email', "staff@{$spec['slug']}.test")->first();
                $salesDept = Department::query()->where('code', 'SAL')->first();

                app(CreateEmployee::class)->handle(new EmployeeData(
                    userId: $staff?->getKey(),
                    branchId: null,
                    departmentId: $salesDept?->getKey(),
                    designationId: null,
                    employeeCode: 'EMP-0001',
                    firstName: 'Sample',
                    lastName: 'Employee',
                    email: "employee@{$spec['slug']}.test",
                    phone: null,
                    hireDate: now()->subYear()->format('Y-m-d'),
                    employmentStatus: EmploymentStatus::Active,
                ));
            }

            $context->clear();
            $registrar->setPermissionsTeamId(null);
        }
    }
}
