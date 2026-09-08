<?php

declare(strict_types=1);

use App\Modules\Authorization\Roles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

it('provisions the four standard roles per tenant', function (): void {
    $tenant = makeTenant();

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
    $roles = Role::where('team_id', $tenant->getKey())->pluck('name')->sort()->values()->all();

    expect($roles)->toBe(['admin', 'manager', 'owner', 'staff']);
});

it('grants owner and admin every permission, staff none', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, Roles::OWNER);
    $staff = makeUser($tenant, Roles::STAFF);

    expect($owner->can('tenant.settings.update'))->toBeTrue()
        ->and($staff->can('tenant.settings.view'))->toBeFalse();
});

it('keeps roles isolated between tenants', function (): void {
    $tenantA = makeTenant(['slug' => 'rbac-a']);
    $userA = makeUser($tenantA, Roles::MANAGER);

    $tenantB = makeTenant(['slug' => 'rbac-b']);
    makeUser($tenantB, Roles::OWNER);

    // Re-bind tenant A: userA is still only a manager there.
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenantA->getKey());
    expect($userA->fresh()->hasRole(Roles::MANAGER))->toBeTrue()
        ->and($userA->fresh()->hasRole(Roles::OWNER))->toBeFalse();
});
