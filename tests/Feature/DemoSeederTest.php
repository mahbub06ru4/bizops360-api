<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Tenant;
use Database\Seeders\DemoSeeder;

it('links every demo role to an employee record and seeds leave types', function (): void {
    (new DemoSeeder)->run();

    $tenant = Tenant::query()->where('slug', 'wanderlust')->firstOrFail();

    expect(LeaveType::query()->where('tenant_id', $tenant->getKey())->count())->toBe(3);

    foreach (['owner', 'manager', 'staff'] as $role) {
        $user = User::query()->where('email', "{$role}@wanderlust.test")->firstOrFail();

        expect(Employee::query()->where('tenant_id', $tenant->getKey())->where('user_id', $user->getKey())->exists())
            ->toBeTrue();
    }
});
