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
            'attendance.manage',

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

            'crm.view_dashboard',

            'income.view',
            'income.create',
            'income.update',
            'income.delete',

            'expense.view',
            'expense.create',
            'expense.update',
            'expense.delete',
            'expense.approve',

            'invoice.view',
            'invoice.create',
            'invoice.update',
            'invoice.delete',
            'invoice.send',
            'invoice.void',
            'invoice.record_payment',
            'invoice.refund',

            'finance.view_reports',

            'traveller.view',
            'traveller.view_all',
            'traveller.create',
            'traveller.update',
            'traveller.delete',

            'visa_application.view',
            'visa_application.view_all',
            'visa_application.create',
            'visa_application.update',
            'visa_application.submit',
            'visa_application.decide',
            'visa_application.delete',

            'booking.view',
            'booking.view_all',
            'booking.create',
            'booking.update',
            'booking.issue',
            'booking.cancel',
            'booking.refund',
            'booking.invoice',
            'booking.delete',

            'travel.view_dashboard',

            'real_estate_project.view',
            'real_estate_project.view_all',
            'real_estate_project.create',
            'real_estate_project.update',
            'real_estate_project.submit',
            'real_estate_project.delete',

            'building.view',
            'building.create',
            'building.update',
            'building.delete',

            'unit.view',
            'unit.create',
            'unit.update',
            'unit.delete',

            'amenity.view',
            'amenity.create',
            'amenity.delete',

            'project_document.view',
            'project_document.upload',

            'land_record.view',
            'land_record.manage',

            'property_requirement.view',
            'property_requirement.create',
            'property_requirement.update',

            'site_visit.view',
            'site_visit.create',
            'site_visit.update',

            'offer.view',
            'offer.create',
            'offer.update',

            'real_estate_booking.view',
            'real_estate_booking.create',
            'real_estate_booking.update',

            'installment.view',
            'installment.manage',

            'real_estate.view_dashboard',

            'audit.view',

            'billing.view',
            'billing.manage',
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
                'attendance.record', 'attendance.manage_settings', 'attendance.manage',
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
                'follow_up.update', 'follow_up.delete', 'crm.view_dashboard',
                'income.view', 'income.create', 'income.update',
                'expense.view', 'expense.create', 'expense.update', 'expense.approve',
                'invoice.view', 'invoice.create', 'invoice.update',
                'invoice.send', 'invoice.record_payment', 'finance.view_reports',
                'traveller.view', 'traveller.view_all', 'traveller.create', 'traveller.update',
                'visa_application.view', 'visa_application.view_all', 'visa_application.create',
                'visa_application.update', 'visa_application.submit', 'visa_application.decide',
                'booking.view', 'booking.view_all', 'booking.create', 'booking.update',
                'booking.issue', 'booking.cancel', 'booking.invoice', 'travel.view_dashboard',
                'real_estate_project.view', 'real_estate_project.view_all', 'real_estate_project.create',
                'real_estate_project.update', 'real_estate_project.submit',
                'building.view', 'building.create', 'building.update', 'building.delete',
                'unit.view', 'unit.create', 'unit.update', 'unit.delete',
                'amenity.view', 'amenity.create', 'amenity.delete',
                'project_document.view', 'project_document.upload',
                'property_requirement.view', 'property_requirement.create', 'property_requirement.update',
                'site_visit.view', 'site_visit.create', 'site_visit.update',
                'offer.view', 'offer.create', 'offer.update',
                'real_estate_booking.view', 'real_estate_booking.create', 'real_estate_booking.update',
                'installment.view', 'installment.manage',
                'real_estate.view_dashboard',
                'audit.view', 'billing.view',
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
                'traveller.view',
                'traveller.create',
                'traveller.update',
                'visa_application.view',
                'visa_application.create',
                'visa_application.update',
                'booking.view',
                'booking.create',
                'booking.update',
                'real_estate_project.view',
                'real_estate_project.create',
                'real_estate_project.update',
                'building.view',
                'building.create',
                'building.update',
                'unit.view',
                'unit.create',
                'unit.update',
                'amenity.view',
                'amenity.create',
                'project_document.view',
                'project_document.upload',
                'property_requirement.view',
                'property_requirement.create',
                'property_requirement.update',
                'site_visit.view',
                'site_visit.create',
                'site_visit.update',
                'offer.view',
                'offer.create',
                'offer.update',
                'real_estate_booking.view',
                'real_estate_booking.create',
                'real_estate_booking.update',
                'installment.view',
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
