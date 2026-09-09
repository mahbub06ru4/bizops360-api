<?php

declare(strict_types=1);

namespace App\Modules\Authorization;

/**
 * The fixed set of roles every tenant has, and the permissions each role holds.
 *
 * Permissions are seeded per tenant (spatie teams mode, team_id = tenant_id).
 * Later phases add their resource permissions to {@see self::permissions()} and
 * grant them to roles in {@see self::grants()}.
 */
final class Roles
{
    public const OWNER = 'owner';

    public const ADMIN = 'admin';

    public const MANAGER = 'manager';

    public const STAFF = 'staff';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::OWNER, self::ADMIN, self::MANAGER, self::STAFF];
    }

    /**
     * Every permission known to the platform. Phase 0 ships the tenant-settings
     * permission as a worked example; feature phases append their own.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            'tenant.settings.view',
            'tenant.settings.update',

            'branch.view',
            'branch.create',
            'branch.update',
            'branch.delete',

            'department.view',
            'department.create',
            'department.update',
            'department.delete',

            'designation.view',
            'designation.create',
            'designation.update',
            'designation.delete',
        ];
    }

    /**
     * role => permissions. The single value '*' means every permission.
     *
     * @return array<string, list<string>>
     */
    public static function grants(): array
    {
        return [
            self::OWNER => ['*'],
            self::ADMIN => ['*'],
            self::MANAGER => [
                'tenant.settings.view',
                'branch.view', 'branch.create', 'branch.update',
                'department.view', 'department.create', 'department.update',
                'designation.view', 'designation.create', 'designation.update',
            ],
            self::STAFF => [
                'branch.view',
                'department.view',
                'designation.view',
            ],
        ];
    }

    /**
     * Resolve the concrete permission list for a role.
     *
     * @return list<string>
     */
    public static function permissionsFor(string $role): array
    {
        $grant = self::grants()[$role] ?? [];

        if ($grant === ['*']) {
            return self::permissions();
        }

        return $grant;
    }
}
