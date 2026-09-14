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
        // A named list here (like MANAGER above) breaks every time a module
        // phase adds a staff-visible permission elsewhere — assert the
        // handful that matter for staff's "view + create/update own work,
        // never delete/approve" shape instead of the full catalogue.
        ->toContain('branch.view', 'employee.view', 'task.create', 'lead.create', 'customer.create')
        ->not->toContain('branch.delete', 'employee.terminate', 'leave.approve', 'lead.delete', 'customer.delete');
});

it('keeps every explicit grant within the known permission catalogue', function (): void {
    foreach ([Roles::MANAGER, Roles::STAFF] as $role) {
        expect(array_diff(Roles::permissionsFor($role), Roles::permissions()))->toBe([]);
    }
});
