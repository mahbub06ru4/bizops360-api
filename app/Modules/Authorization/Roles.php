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

            'employee.view',
            'employee.create',
            'employee.update',
            'employee.terminate',
            'employee.delete',

            'team.view',
            'team.create',
            'team.update',
            'team.delete',

            'user.view',
            'user.create',
            'user.update',
            'user.assign_roles',
            'user.delete',
            'role.view',

            'holiday.view',
            'holiday.create',
            'holiday.update',
            'holiday.delete',

            'leave_type.view',
            'leave_type.create',
            'leave_type.update',
            'leave_type.delete',

            'leave.view',
            'leave.request',
            'leave.approve',
            'leave.manage_balance',

            'attendance.view',
            'attendance.view_all',
            'attendance.check_in',
            'attendance.record',
            'attendance.manage_settings',

            'employee_document.view',
            'employee_document.view_all',
            'employee_document.upload',
            'employee_document.delete',

            'project.view',
            'project.create',
            'project.update',
            'project.delete',

            'task.view',
            'task.view_all',
            'task.create',
            'task.update',
            'task.assign',
            'task.delete',

            'operations.view_dashboard',

            'lead.view',
            'lead.view_all',
            'lead.create',
            'lead.update',
            'lead.delete',
            'lead.convert',

            'customer.view',
            'customer.view_all',
            'customer.create',
            'customer.update',
            'customer.delete',

            'follow_up.view',
            'follow_up.view_all',
            'follow_up.create',
            'follow_up.update',
            'follow_up.delete',
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
                'employee.view', 'employee.create', 'employee.update', 'employee.terminate',
                'team.view', 'team.create', 'team.update',
                'user.view', 'role.view',
                'holiday.view', 'holiday.create', 'holiday.update',
                'leave_type.view', 'leave_type.create', 'leave_type.update',
                'leave.view', 'leave.request', 'leave.approve', 'leave.manage_balance',
                'attendance.view', 'attendance.view_all', 'attendance.check_in',
                'attendance.record', 'attendance.manage_settings',
                'employee_document.view', 'employee_document.view_all',
                'employee_document.upload', 'employee_document.delete',
                'project.view', 'project.create', 'project.update', 'project.delete',
                'task.view', 'task.view_all', 'task.create', 'task.update',
                'task.assign', 'task.delete', 'operations.view_dashboard',
                'lead.view', 'lead.view_all', 'lead.create', 'lead.update',
                'lead.delete', 'lead.convert',
                'customer.view', 'customer.view_all', 'customer.create',
                'customer.update', 'customer.delete',
                'follow_up.view', 'follow_up.view_all', 'follow_up.create',
                'follow_up.update', 'follow_up.delete',
            ],
            self::STAFF => [
                'branch.view',
                'department.view',
                'designation.view',
                'employee.view',
                'team.view',
                'holiday.view',
                'leave_type.view',
                'leave.view',
                'leave.request',
                'attendance.view',
                'attendance.check_in',
                'employee_document.view',
                'project.view',
                'task.view',
                'task.create',
                'task.update',
                'lead.view',
                'lead.create',
                'lead.update',
                'customer.view',
                'customer.create',
                'customer.update',
                'follow_up.view',
                'follow_up.create',
                'follow_up.update',
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
