<?php

declare(strict_types=1);

namespace App\Modules\AdminUi\Domain;

/**
 * Describes the admin panel's modules/resources/fields so the frontend can
 * render nav, list tables, and create/edit forms without hardcoding any of
 * it — the panel becomes a generic renderer driven by this metadata instead
 * of one hand-written page per resource.
 *
 * Each resource here mirrors an existing Form Request (validation source of
 * truth stays server-side) and API Resource (response shape) exactly; this
 * registry does not replace those, it just describes them for the UI.
 *
 * Field `type` values the frontend understands: string, text, number,
 * boolean, date, select (static `options`), password (masked input, never
 * pre-filled on edit), relation (`relation.resource` points at another
 * resource key in this same registry, fetched as a dropdown of {id, label}).
 * `onlyOnCreate: true` hides a field on the edit form entirely (e.g. a
 * password that's set once, not re-editable this way).
 *
 * A resource's `permissions.create`/`update`/`delete` may be `null` when no
 * matching backend route exists (e.g. LeaveRequest has no update/destroy
 * route) — the frontend hides that action rather than gating it on a
 * permission that would never be enough anyway.
 */
class AdminSchemaRegistry
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'modules' => [
                [
                    'key' => 'organization',
                    'label' => 'Organization',
                    'icon' => 'Building2',
                    'resources' => ['branches', 'departments', 'designations', 'employees', 'teams', 'users', 'roles'],
                ],
                [
                    'key' => 'hr',
                    'label' => 'HR',
                    'icon' => 'Users',
                    'resources' => [
                        'holidays', 'leave_types', 'leave_requests', 'leave_balances', 'attendance',
                        'attendance_settings', 'office_location', 'employee_documents',
                    ],
                ],
                [
                    'key' => 'operations',
                    'label' => 'Operations',
                    'icon' => 'ClipboardList',
                    'resources' => ['projects', 'tasks'],
                ],
                [
                    'key' => 'crm',
                    'label' => 'CRM',
                    'icon' => 'Handshake',
                    'resources' => ['leads', 'customers', 'follow_ups'],
                ],
                [
                    'key' => 'finance',
                    'label' => 'Finance',
                    'icon' => 'Wallet',
                    'resources' => ['incomes', 'expenses', 'invoices'],
                ],
            ],
            // Custom analytics/summary screens that aren't a resource list —
            // no generic table or form could render these meaningfully, so
            // they stay hand-built pages. Listed here only so the dynamic
            // nav can surface them under the right module instead of a
            // human having to remember they exist outside the schema.
            'dashboards' => [
                ['key' => 'operations_overview', 'label' => 'Overview', 'href' => '/operations/overview', 'module' => 'operations', 'permission' => 'operations.view_dashboard'],
                ['key' => 'crm_reports', 'label' => 'Reports', 'href' => '/crm/reports', 'module' => 'crm', 'permission' => 'crm.view_dashboard'],
                ['key' => 'finance_reports', 'label' => 'Reports', 'href' => '/finance/reports', 'module' => 'finance', 'permission' => 'finance.view_reports'],
            ],
            'resources' => [
                $this->branches(),
                $this->departments(),
                $this->designations(),
                $this->employees(),
                $this->teams(),
                $this->users(),
                $this->roles(),
                $this->holidays(),
                $this->leaveTypes(),
                $this->leaveRequests(),
                $this->leaveBalances(),
                $this->attendance(),
                $this->attendanceSettings(),
                $this->officeLocation(),
                $this->employeeDocuments(),
                $this->projects(),
                $this->tasks(),
                $this->leads(),
                $this->customers(),
                $this->followUps(),
                $this->incomes(),
                $this->expenses(),
                $this->invoices(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function branches(): array
    {
        return [
            'key' => 'branches',
            'labelField' => 'name',
            'label' => 'Branch',
            'pluralLabel' => 'Branches',
            'endpoint' => '/branches',
            'permissions' => ['view' => 'branch.view', 'create' => 'branch.create', 'update' => 'branch.update', 'delete' => 'branch.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'is_head_office', 'label' => 'Head office'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'code', 'label' => 'Code', 'type' => 'string', 'required' => true],
                ['key' => 'address', 'label' => 'Address', 'type' => 'string', 'required' => false],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string', 'required' => false],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => false],
                ['key' => 'is_head_office', 'label' => 'Head office', 'type' => 'boolean', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function departments(): array
    {
        return [
            'key' => 'departments',
            'labelField' => 'name',
            'label' => 'Department',
            'pluralLabel' => 'Departments',
            'endpoint' => '/departments',
            'permissions' => ['view' => 'department.view', 'create' => 'department.create', 'update' => 'department.update', 'delete' => 'department.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'branch.name', 'label' => 'Branch'],
                ['key' => 'description', 'label' => 'Description'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'code', 'label' => 'Code', 'type' => 'string', 'required' => true],
                ['key' => 'branch_id', 'label' => 'Branch', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'branches']],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function designations(): array
    {
        return [
            'key' => 'designations',
            'labelField' => 'title',
            'label' => 'Designation',
            'pluralLabel' => 'Designations',
            'endpoint' => '/designations',
            'permissions' => ['view' => 'designation.view', 'create' => 'designation.create', 'update' => 'designation.update', 'delete' => 'designation.delete'],
            'columns' => [
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'department.name', 'label' => 'Department'],
                ['key' => 'rank', 'label' => 'Rank'],
            ],
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'string', 'required' => true],
                ['key' => 'department_id', 'label' => 'Department', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'departments']],
                ['key' => 'rank', 'label' => 'Rank', 'type' => 'number', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function employees(): array
    {
        return [
            'key' => 'employees',
            'labelField' => 'full_name',
            'label' => 'Employee',
            'pluralLabel' => 'Employees',
            'endpoint' => '/employees',
            'permissions' => ['view' => 'employee.view', 'create' => 'employee.create', 'update' => 'employee.update', 'delete' => 'employee.delete'],
            'searchable' => true,
            'columns' => [
                ['key' => 'full_name', 'label' => 'Name'],
                ['key' => 'employee_code', 'label' => 'Code'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'department.name', 'label' => 'Department'],
                ['key' => 'designation.title', 'label' => 'Designation'],
                ['key' => 'employment_status', 'label' => 'Status'],
            ],
            'fields' => [
                ['key' => 'first_name', 'label' => 'First name', 'type' => 'string', 'required' => true],
                ['key' => 'last_name', 'label' => 'Last name', 'type' => 'string', 'required' => true],
                ['key' => 'employee_code', 'label' => 'Employee code', 'type' => 'string', 'required' => true],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => false],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string', 'required' => false],
                ['key' => 'hire_date', 'label' => 'Hire date', 'type' => 'date', 'required' => true],
                ['key' => 'branch_id', 'label' => 'Branch', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'branches']],
                ['key' => 'department_id', 'label' => 'Department', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'departments']],
                ['key' => 'designation_id', 'label' => 'Designation', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'designations']],
                [
                    'key' => 'employment_status', 'label' => 'Status', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'probation', 'label' => 'Probation'],
                        ['value' => 'on_leave', 'label' => 'On leave'],
                        ['value' => 'terminated', 'label' => 'Terminated'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teams(): array
    {
        return [
            'key' => 'teams',
            'labelField' => 'name',
            'label' => 'Team',
            'pluralLabel' => 'Teams',
            'endpoint' => '/teams',
            'permissions' => ['view' => 'team.view', 'create' => 'team.create', 'update' => 'team.update', 'delete' => 'team.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'members_count', 'label' => 'Members'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'required' => false],
                ['key' => 'lead_employee_id', 'label' => 'Lead', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
            ],
        ];
    }

    /**
     * Roles/password reset stay on the hand-built Users screen — creating a
     * login here only covers name/email/password, no role assignment.
     *
     * @return array<string, mixed>
     */
    private function users(): array
    {
        return [
            'key' => 'users',
            'labelField' => 'name',
            'label' => 'User',
            'pluralLabel' => 'Users',
            'endpoint' => '/users',
            'permissions' => ['view' => 'user.view', 'create' => 'user.create', 'update' => 'user.update', 'delete' => 'user.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'roles', 'label' => 'Roles'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => true],
                ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'onlyOnCreate' => true],
                [
                    'key' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password',
                    'required' => true, 'onlyOnCreate' => true,
                ],
            ],
        ];
    }

    /**
     * The fixed 4-role catalogue every tenant is provisioned with — view
     * only, and not paginated (RoleController returns a plain collection,
     * not paginate()), so the frontend must not expect a `meta` envelope.
     *
     * @return array<string, mixed>
     */
    private function roles(): array
    {
        return [
            'key' => 'roles',
            'labelField' => 'name',
            'label' => 'Role',
            'pluralLabel' => 'Roles',
            'endpoint' => '/roles',
            'paginated' => false,
            'permissions' => ['view' => 'role.view', 'create' => null, 'update' => null, 'delete' => null],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'permissions', 'label' => 'Permissions'],
            ],
            'fields' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function holidays(): array
    {
        return [
            'key' => 'holidays',
            'labelField' => 'name',
            'label' => 'Holiday',
            'pluralLabel' => 'Holidays',
            'endpoint' => '/holidays',
            'permissions' => ['view' => 'holiday.view', 'create' => 'holiday.create', 'update' => 'holiday.update', 'delete' => 'holiday.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'is_recurring', 'label' => 'Recurring'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                ['key' => 'is_recurring', 'label' => 'Recurs every year', 'type' => 'boolean', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leaveTypes(): array
    {
        return [
            'key' => 'leave_types',
            'labelField' => 'name',
            'label' => 'Leave type',
            'pluralLabel' => 'Leave types',
            'endpoint' => '/leave-types',
            'permissions' => ['view' => 'leave_type.view', 'create' => 'leave_type.create', 'update' => 'leave_type.update', 'delete' => 'leave_type.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'default_days_per_year', 'label' => 'Days/year'],
                ['key' => 'is_paid', 'label' => 'Paid'],
                ['key' => 'requires_approval', 'label' => 'Needs approval'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'code', 'label' => 'Code', 'type' => 'string', 'required' => true],
                ['key' => 'default_days_per_year', 'label' => 'Default days/year', 'type' => 'number', 'required' => false],
                ['key' => 'is_paid', 'label' => 'Paid', 'type' => 'boolean', 'required' => false],
                ['key' => 'requires_approval', 'label' => 'Requires approval', 'type' => 'boolean', 'required' => false],
            ],
        ];
    }

    /**
     * No update/destroy route (approve/reject/cancel are the only ways a
     * leave request changes after it's filed) — list + create only.
     *
     * @return array<string, mixed>
     */
    private function leaveRequests(): array
    {
        return [
            'key' => 'leave_requests',
            'labelField' => 'id',
            'label' => 'Leave request',
            'pluralLabel' => 'Leave requests',
            'endpoint' => '/leave-requests',
            'permissions' => ['view' => 'leave.view', 'create' => 'leave.request', 'update' => null, 'delete' => null],
            'columns' => [
                ['key' => 'employee.full_name', 'label' => 'Employee'],
                ['key' => 'leave_type.name', 'label' => 'Type'],
                ['key' => 'start_date', 'label' => 'Start'],
                ['key' => 'end_date', 'label' => 'End'],
                ['key' => 'days', 'label' => 'Days'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'fields' => [
                ['key' => 'employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                ['key' => 'leave_type_id', 'label' => 'Leave type', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'leave_types']],
                ['key' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'required' => true],
                ['key' => 'end_date', 'label' => 'End date', 'type' => 'date', 'required' => true],
                ['key' => 'reason', 'label' => 'Reason', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * No id-based update route — balances are set via a bare PUT with the
     * employee/leave-type/year as the key, not /leave-balances/{id}. List
     * only, for now.
     *
     * @return array<string, mixed>
     */
    private function leaveBalances(): array
    {
        return [
            'key' => 'leave_balances',
            'labelField' => 'id',
            'label' => 'Leave balance',
            'pluralLabel' => 'Leave balances',
            'endpoint' => '/leave-balances',
            'permissions' => ['view' => 'leave.view', 'create' => null, 'update' => null, 'delete' => null],
            'columns' => [
                ['key' => 'employee.full_name', 'label' => 'Employee'],
                ['key' => 'leave_type.name', 'label' => 'Type'],
                ['key' => 'year', 'label' => 'Year'],
                ['key' => 'entitled_days', 'label' => 'Entitled'],
                ['key' => 'used_days', 'label' => 'Used'],
                ['key' => 'remaining_days', 'label' => 'Remaining'],
            ],
            'fields' => [],
        ];
    }

    /**
     * No update/destroy route — attendance is corrected by recording a new
     * row for the day, not editing the old one. List + create only.
     *
     * @return array<string, mixed>
     */
    private function attendance(): array
    {
        return [
            'key' => 'attendance',
            'labelField' => 'id',
            'label' => 'Attendance record',
            'pluralLabel' => 'Attendance',
            'endpoint' => '/attendance',
            'permissions' => ['view' => 'attendance.view', 'create' => 'attendance.record', 'update' => null, 'delete' => null],
            'columns' => [
                ['key' => 'employee.full_name', 'label' => 'Employee'],
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'check_in_at', 'label' => 'Check-in'],
                ['key' => 'check_out_at', 'label' => 'Check-out'],
                ['key' => 'worked_minutes', 'label' => 'Worked (min)'],
            ],
            'fields' => [
                ['key' => 'employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'employees']],
                ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                [
                    'key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true,
                    'options' => [
                        ['value' => 'present', 'label' => 'Present'],
                        ['value' => 'late', 'label' => 'Late'],
                        ['value' => 'absent', 'label' => 'Absent'],
                        ['value' => 'half_day', 'label' => 'Half day'],
                        ['value' => 'on_leave', 'label' => 'On leave'],
                        ['value' => 'holiday', 'label' => 'Holiday'],
                    ],
                ],
                ['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * A single tenant-wide record with no id — GET/PUT on the bare endpoint,
     * no list. The frontend renders this as one inline edit form, not a
     * table.
     *
     * @return array<string, mixed>
     */
    private function attendanceSettings(): array
    {
        return [
            'key' => 'attendance_settings',
            'labelField' => 'work_starts_at',
            'label' => 'Attendance settings',
            'pluralLabel' => 'Attendance settings',
            'endpoint' => '/attendance-settings',
            'mode' => 'singleton',
            'permissions' => ['view' => 'attendance.view', 'create' => null, 'update' => 'attendance.manage_settings', 'delete' => null],
            'columns' => [],
            'fields' => [
                ['key' => 'work_starts_at', 'label' => 'Work starts at', 'type' => 'time', 'required' => true],
                ['key' => 'work_ends_at', 'label' => 'Work ends at', 'type' => 'time', 'required' => true],
                ['key' => 'grace_minutes', 'label' => 'Grace period (minutes)', 'type' => 'number', 'required' => true],
            ],
        ];
    }

    /**
     * Singleton, like attendance_settings — the geofence staff must be
     * inside to check in.
     *
     * @return array<string, mixed>
     */
    private function officeLocation(): array
    {
        return [
            'key' => 'office_location',
            'labelField' => 'label',
            'label' => 'Office location',
            'pluralLabel' => 'Office location',
            'endpoint' => '/office-location',
            'mode' => 'singleton',
            'permissions' => ['view' => 'attendance.view', 'create' => null, 'update' => 'attendance.manage', 'delete' => null],
            'columns' => [],
            'fields' => [
                ['key' => 'label', 'label' => 'Label', 'type' => 'string', 'required' => true],
                ['key' => 'latitude', 'label' => 'Latitude', 'type' => 'number', 'required' => true],
                ['key' => 'longitude', 'label' => 'Longitude', 'type' => 'number', 'required' => true],
                ['key' => 'radius_meters', 'label' => 'Radius (meters)', 'type' => 'number', 'required' => true],
                ['key' => 'start_time', 'label' => 'Start time', 'type' => 'time', 'required' => true],
                ['key' => 'end_time', 'label' => 'End time', 'type' => 'time', 'required' => true],
            ],
        ];
    }

    /**
     * No update route — a document is replaced by uploading a new one and
     * deleting the old, not edited in place. List + create (upload) +
     * delete only.
     *
     * @return array<string, mixed>
     */
    private function employeeDocuments(): array
    {
        return [
            'key' => 'employee_documents',
            'labelField' => 'title',
            'label' => 'Employee document',
            'pluralLabel' => 'Employee documents',
            'endpoint' => '/employee-documents',
            'permissions' => ['view' => 'employee_document.view', 'create' => 'employee_document.upload', 'update' => null, 'delete' => 'employee_document.delete'],
            'columns' => [
                ['key' => 'employee.full_name', 'label' => 'Employee'],
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'expires_at', 'label' => 'Expires'],
                ['key' => 'is_expired', 'label' => 'Expired'],
            ],
            'fields' => [
                ['key' => 'employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'employees']],
                [
                    'key' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => true,
                    'options' => [
                        ['value' => 'nid', 'label' => 'National ID'],
                        ['value' => 'passport', 'label' => 'Passport'],
                        ['value' => 'contract', 'label' => 'Contract'],
                        ['value' => 'offer_letter', 'label' => 'Offer letter'],
                        ['value' => 'certificate', 'label' => 'Certificate'],
                        ['value' => 'other', 'label' => 'Other'],
                    ],
                ],
                ['key' => 'title', 'label' => 'Title', 'type' => 'string', 'required' => true],
                ['key' => 'expires_at', 'label' => 'Expires on', 'type' => 'date', 'required' => false],
                ['key' => 'file', 'label' => 'File', 'type' => 'file', 'required' => true, 'onlyOnCreate' => true],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function projects(): array
    {
        return [
            'key' => 'projects',
            'labelField' => 'name',
            'label' => 'Project',
            'pluralLabel' => 'Projects',
            'endpoint' => '/projects',
            'permissions' => ['view' => 'project.view', 'create' => 'project.create', 'update' => 'project.update', 'delete' => 'project.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'department.name', 'label' => 'Department'],
                ['key' => 'lead.full_name', 'label' => 'Lead'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'code', 'label' => 'Code', 'type' => 'string', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'required' => false],
                [
                    'key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'planning', 'label' => 'Planning'],
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'on_hold', 'label' => 'On hold'],
                        ['value' => 'completed', 'label' => 'Completed'],
                        ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ],
                ],
                ['key' => 'department_id', 'label' => 'Department', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'departments']],
                ['key' => 'lead_employee_id', 'label' => 'Lead', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                ['key' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'required' => false],
                ['key' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tasks(): array
    {
        return [
            'key' => 'tasks',
            'labelField' => 'title',
            'label' => 'Task',
            'pluralLabel' => 'Tasks',
            'endpoint' => '/tasks',
            'permissions' => ['view' => 'task.view', 'create' => 'task.create', 'update' => 'task.update', 'delete' => 'task.delete'],
            'columns' => [
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'project.name', 'label' => 'Project'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'priority', 'label' => 'Priority'],
                ['key' => 'due_at', 'label' => 'Due'],
            ],
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'string', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'required' => false],
                ['key' => 'project_id', 'label' => 'Project', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'projects']],
                [
                    'key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'low', 'label' => 'Low'],
                        ['value' => 'normal', 'label' => 'Normal'],
                        ['value' => 'high', 'label' => 'High'],
                        ['value' => 'urgent', 'label' => 'Urgent'],
                    ],
                ],
                ['key' => 'due_at', 'label' => 'Due', 'type' => 'date', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leads(): array
    {
        return [
            'key' => 'leads',
            'labelField' => 'name',
            'label' => 'Lead',
            'pluralLabel' => 'Leads',
            'endpoint' => '/leads',
            'permissions' => ['view' => 'lead.view', 'create' => 'lead.create', 'update' => 'lead.update', 'delete' => 'lead.delete'],
            'searchable' => false,
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'company', 'label' => 'Company'],
                ['key' => 'stage', 'label' => 'Stage'],
                ['key' => 'estimated_value', 'label' => 'Value'],
                ['key' => 'owner.full_name', 'label' => 'Owner'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'company', 'label' => 'Company', 'type' => 'string', 'required' => false],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => false],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string', 'required' => false],
                ['key' => 'source', 'label' => 'Source', 'type' => 'string', 'required' => false],
                ['key' => 'estimated_value', 'label' => 'Estimated value', 'type' => 'number', 'required' => false],
                ['key' => 'owner_employee_id', 'label' => 'Owner', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                ['key' => 'notes', 'label' => 'Notes', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customers(): array
    {
        return [
            'key' => 'customers',
            'labelField' => 'name',
            'label' => 'Customer',
            'pluralLabel' => 'Customers',
            'endpoint' => '/customers',
            'permissions' => ['view' => 'customer.view', 'create' => 'customer.create', 'update' => 'customer.update', 'delete' => 'customer.delete'],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'type', 'label' => 'Type'],
                ['key' => 'company', 'label' => 'Company'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'owner.full_name', 'label' => 'Owner'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                [
                    'key' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'individual', 'label' => 'Individual'],
                        ['value' => 'business', 'label' => 'Business'],
                    ],
                ],
                ['key' => 'company', 'label' => 'Company', 'type' => 'string', 'required' => false],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => false],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string', 'required' => false],
                ['key' => 'address', 'label' => 'Address', 'type' => 'string', 'required' => false],
                ['key' => 'owner_employee_id', 'label' => 'Owner', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
            ],
        ];
    }

    /**
     * No flat create route — a follow-up is always scheduled under a lead
     * or customer, not created standalone. List + update + delete only.
     *
     * @return array<string, mixed>
     */
    private function followUps(): array
    {
        return [
            'key' => 'follow_ups',
            'labelField' => 'id',
            'label' => 'Follow-up',
            'pluralLabel' => 'Follow-ups',
            'endpoint' => '/follow-ups',
            'permissions' => ['view' => 'follow_up.view', 'create' => null, 'update' => 'follow_up.update', 'delete' => 'follow_up.delete'],
            'columns' => [
                ['key' => 'type', 'label' => 'Type'],
                ['key' => 'due_at', 'label' => 'Due'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'assigned_employee.full_name', 'label' => 'Assigned to'],
            ],
            'fields' => [
                [
                    'key' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'call', 'label' => 'Call'],
                        ['value' => 'email', 'label' => 'Email'],
                        ['value' => 'meeting', 'label' => 'Meeting'],
                        ['value' => 'task', 'label' => 'Task'],
                    ],
                ],
                ['key' => 'due_at', 'label' => 'Due', 'type' => 'date', 'required' => true],
                ['key' => 'assigned_employee_id', 'label' => 'Assigned to', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                ['key' => 'notes', 'label' => 'Notes', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function incomes(): array
    {
        return [
            'key' => 'incomes',
            'labelField' => 'source',
            'label' => 'Income',
            'pluralLabel' => 'Income',
            'endpoint' => '/incomes',
            'permissions' => ['view' => 'income.view', 'create' => 'income.create', 'update' => 'income.update', 'delete' => 'income.delete'],
            'columns' => [
                ['key' => 'source', 'label' => 'Source'],
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'received_on', 'label' => 'Received on'],
                ['key' => 'customer.name', 'label' => 'Customer'],
            ],
            'fields' => [
                [
                    'key' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'customer_payment', 'label' => 'Customer payment'],
                        ['value' => 'other', 'label' => 'Other'],
                    ],
                ],
                ['key' => 'source', 'label' => 'Source', 'type' => 'string', 'required' => false],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                ['key' => 'received_on', 'label' => 'Received on', 'type' => 'date', 'required' => true],
                ['key' => 'customer_id', 'label' => 'Customer', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'customers']],
                ['key' => 'reference', 'label' => 'Reference', 'type' => 'string', 'required' => false],
                ['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function expenses(): array
    {
        return [
            'key' => 'expenses',
            'labelField' => 'title',
            'label' => 'Expense',
            'pluralLabel' => 'Expenses',
            'endpoint' => '/expenses',
            'permissions' => ['view' => 'expense.view', 'create' => 'expense.create', 'update' => 'expense.update', 'delete' => 'expense.delete'],
            'columns' => [
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'spent_on', 'label' => 'Spent on'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'string', 'required' => true],
                [
                    'key' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => false,
                    'options' => [
                        ['value' => 'office', 'label' => 'Office'],
                        ['value' => 'employee', 'label' => 'Employee'],
                        ['value' => 'supplier', 'label' => 'Supplier'],
                        ['value' => 'other', 'label' => 'Other'],
                    ],
                ],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                ['key' => 'spent_on', 'label' => 'Spent on', 'type' => 'date', 'required' => true],
                ['key' => 'employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                ['key' => 'supplier_name', 'label' => 'Supplier', 'type' => 'string', 'required' => false],
                ['key' => 'reference', 'label' => 'Reference', 'type' => 'string', 'required' => false],
                ['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function invoices(): array
    {
        return [
            'key' => 'invoices',
            'labelField' => 'number',
            'label' => 'Invoice',
            'pluralLabel' => 'Invoices',
            'endpoint' => '/invoices',
            'permissions' => ['view' => 'invoice.view', 'create' => 'invoice.create', 'update' => 'invoice.update', 'delete' => 'invoice.delete'],
            'columns' => [
                ['key' => 'number', 'label' => 'Number'],
                ['key' => 'customer_name', 'label' => 'Customer'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'amount_due', 'label' => 'Due'],
            ],
            'fields' => [
                ['key' => 'customer_id', 'label' => 'Customer', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'customers']],
                ['key' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'required' => true],
                ['key' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'required' => false],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                ['key' => 'notes', 'label' => 'Notes', 'type' => 'text', 'required' => false],
            ],
        ];
    }
}
