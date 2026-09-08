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

it('resolves explicit and empty grants', function (): void {
    expect(Roles::permissionsFor(Roles::MANAGER))->toBe(['tenant.settings.view'])
        ->and(Roles::permissionsFor(Roles::STAFF))->toBe([]);
});
