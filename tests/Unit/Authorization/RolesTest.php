<?php

declare(strict_types=1);

use App\Modules\Authorization\Roles;

it('lists the four roles', function (): void {
    expect(Roles::all())->toBe(['owner', 'admin', 'manager', 'staff']);
});

it('expands the wildcard grant to every permission', function (): void {
    expect(Roles::permissionsFor(Roles::OWNER))->toBe(Roles::permissions())
        ->and(Roles::permissionsFor(Roles::ADMIN))->toBe(Roles::permissions());
});

it('resolves explicit grants for manager and staff', function (): void {
    expect(Roles::permissionsFor(Roles::MANAGER))
        ->toContain('tenant.settings.view', 'branch.create', 'department.update')
        ->not->toContain('branch.delete')
        ->and(Roles::permissionsFor(Roles::STAFF))
        ->toBe(['branch.view', 'department.view', 'designation.view']);
});

it('keeps every explicit grant within the known permission catalogue', function (): void {
    foreach ([Roles::MANAGER, Roles::STAFF] as $role) {
        expect(array_diff(Roles::permissionsFor($role), Roles::permissions()))->toBe([]);
    }
});
