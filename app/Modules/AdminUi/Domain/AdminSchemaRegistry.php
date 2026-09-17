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
 * boolean, date, time, select (static `options`), multiselect (static
 * `options`, submits an array of values), password (masked input, never
 * pre-filled on edit), file (multipart upload), relation (`relation.resource`
 * points at another resource key in this same registry, fetched as a
 * dropdown of {id, label}), relation-multi (same, but a multi-select
 * submitting an array of ids). `onlyOnCreate: true` hides a field on the
 * edit form entirely (e.g. a password that's set once, not re-editable this
 * way). `prefillFrom` is a dot-path evaluated against the row (or, when the
 * owning action sets `fetchDetail`, against a fresh GET of the record) whose
 * value becomes the field's default when the dialog opens.
 *
 * A resource's `permissions.create`/`update`/`delete` may be `null` when no
 * matching backend route exists (e.g. LeaveRequest has no update/destroy
 * route) — the frontend hides that action rather than gating it on a
 * permission that would never be enough anyway.
 *
 * `actions`: named operations beyond plain create/update/delete — approve/
 * reject, convert, terminate, and the like. Each has a `key`, `label`, HTTP
 * `method`, `endpoint` (may contain a `{id}` placeholder), `permission` (or
 * `null` to always show, gated by the backend instead), optional `confirm`
 * text, optional `style` ('default'|'destructive'|'secondary'), optional
 * `fields` (rendered as a small dialog form; an action with no fields
 * prompts `confirm` then submits immediately), optional `fetchDetail` (GET
 * the record fresh before opening the dialog, for fields whose
 * `prefillFrom` needs data the list row doesn't carry), and optional
 * `scope` ('row', the default — one button per row — or 'resource', a
 * single button near "New X" not tied to any row, for a keyless
 * create-or-update like LeaveBalance's upsert).
 *
 * A column with `link: true` renders its value as a clickable/downloadable
 * link instead of plain text. A resource with `summaryEndpoint` gets a row
 * of stat cards fetched from that endpoint rendered above its table — for
 * read-only aggregate data no generic table/form captures.
 *
 * A resource with `detail` gets its label cell linked to a generic
 * `/admin/{key}/{id}` detail page instead of plain text, built from:
 *   - `fields`: read-only {key, label} pairs shown at the top.
 *   - `relatedLists`: nested sub-resources with their own CRUD — each has
 *     `listEndpoint` (`{id}` = parent id, GET+POST) and `rowEndpoint`
 *     (`{id}` = the row's own id, PUT+DELETE — omit whichever the backend
 *     doesn't support), the usual `permissions`/`columns`/`fields`/
 *     `actions`, and optional `ownerField`/`bypassPermission` (update/
 *     delete show only for the row's own author unless the viewer holds
 *     `bypassPermission` — e.g. task comments).
 *   - `embeddedLists`: read-only tables sourced from an array already
 *     present in the resource's own GET `{id}` response (a `path` dot-path
 *     into it) rather than a separate endpoint — e.g. Invoice's payments/
 *     refunds, which the backend returns eager-loaded on `show()` and (for
 *     refunds) has no standalone list route for at all.
 *   - `activity`: a read-only timeline (`listEndpoint`) plus an optional
 *     freeform note form (`noteEndpoint`, gated by `notePermission`).
 *
 * `charts` (top-level, alongside `resources`) describes the main
 * dashboard's charts — see the inline comment above that key in `build()`.
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
            // The main dashboard's charts, described instead of hand-coded:
            // each names an endpoint, a permission, a chart `type`, and a
            // `dataPath` (dot-path, mirroring `getPath` on the frontend) into
            // that endpoint's response. 'area' charts also need `xKey` (the
            // category field on each data-path row) and `series` (which
            // numeric fields to plot); 'donut'/'bar' charts read `dataPath`
            // as a plain {status: count} object and plot one slice/bar per
            // key. Charts sharing an `endpoint` are fetched once and reused.
            'charts' => [
                [
                    'key' => 'finance_trend', 'label' => 'Income vs. expense', 'type' => 'area',
                    'endpoint' => '/finance/monthly', 'permission' => 'finance.view_reports',
                    'dataPath' => 'months', 'xKey' => 'label',
                    'series' => [
                        ['key' => 'income', 'label' => 'Income'],
                        ['key' => 'expense', 'label' => 'Expense'],
                    ],
                ],
                [
                    'key' => 'tasks_by_status', 'label' => 'Tasks by status', 'type' => 'donut',
                    'endpoint' => '/operations/overview', 'permission' => 'operations.view_dashboard',
                    'dataPath' => 'tasks.by_status',
                ],
                [
                    'key' => 'projects_by_status', 'label' => 'Projects by status', 'type' => 'donut',
                    'endpoint' => '/operations/overview', 'permission' => 'operations.view_dashboard',
                    'dataPath' => 'projects.by_status',
                ],
                [
                    'key' => 'leads_by_stage', 'label' => 'Sales pipeline by stage', 'type' => 'bar',
                    'endpoint' => '/crm/overview', 'permission' => 'crm.view_dashboard',
                    'dataPath' => 'leads.by_stage',
                ],
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
            'actions' => [
                [
                    'key' => 'terminate',
                    'label' => 'Terminate',
                    'method' => 'POST',
                    'endpoint' => '/employees/{id}/terminate',
                    'permission' => 'employee.terminate',
                    'confirm' => 'Terminate this employee? This cannot be undone.',
                    'style' => 'destructive',
                    'fields' => [],
                ],
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
            'actions' => [
                [
                    'key' => 'manage_members',
                    'label' => 'Members',
                    'method' => 'PUT',
                    'endpoint' => '/teams/{id}/members',
                    'permission' => 'team.update',
                    'fetchDetail' => true,
                    'fields' => [
                        [
                            'key' => 'members', 'label' => 'Members', 'type' => 'relation-multi', 'required' => false,
                            'relation' => ['resource' => 'employees'], 'prefillFrom' => 'members',
                        ],
                    ],
                ],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text', 'required' => false],
                ['key' => 'lead_employee_id', 'label' => 'Lead', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
            ],
        ];
    }

    /**
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
            'actions' => [
                [
                    'key' => 'assign_roles',
                    'label' => 'Roles',
                    'method' => 'PUT',
                    'endpoint' => '/users/{id}/roles',
                    'permission' => 'user.assign_roles',
                    'fields' => [
                        [
                            'key' => 'roles', 'label' => 'Roles', 'type' => 'multiselect', 'required' => false,
                            'prefillFrom' => 'roles',
                            'options' => [
                                ['value' => 'owner', 'label' => 'Owner'],
                                ['value' => 'admin', 'label' => 'Admin'],
                                ['value' => 'manager', 'label' => 'Manager'],
                                ['value' => 'staff', 'label' => 'Staff'],
                            ],
                        ],
                    ],
                ],
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
            'actions' => [
                [
                    'key' => 'approve', 'label' => 'Approve', 'method' => 'POST',
                    'endpoint' => '/leave-requests/{id}/approve', 'permission' => 'leave.approve',
                    'confirm' => 'Approve this leave request?',
                    'fields' => [['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false]],
                ],
                [
                    'key' => 'reject', 'label' => 'Reject', 'method' => 'POST',
                    'endpoint' => '/leave-requests/{id}/reject', 'permission' => 'leave.approve', 'style' => 'destructive',
                    'fields' => [['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false]],
                ],
                [
                    'key' => 'cancel', 'label' => 'Cancel', 'method' => 'POST',
                    'endpoint' => '/leave-requests/{id}/cancel', 'permission' => 'leave.request', 'style' => 'secondary',
                    'confirm' => 'Cancel this leave request?', 'fields' => [],
                ],
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
     * employee/leave-type/year as the key, not /leave-balances/{id}. Exposed
     * as a `scope: 'resource'` action (not tied to any one row) instead of
     * `fields`/create permission.
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
            'actions' => [
                [
                    'key' => 'set_balance', 'label' => 'Set balance', 'method' => 'PUT', 'scope' => 'resource',
                    'endpoint' => '/leave-balances', 'permission' => 'leave.manage_balance',
                    'fields' => [
                        ['key' => 'employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'employees']],
                        ['key' => 'leave_type_id', 'label' => 'Leave type', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'leave_types']],
                        ['key' => 'year', 'label' => 'Year', 'type' => 'number', 'required' => true],
                        ['key' => 'entitled_days', 'label' => 'Entitled days', 'type' => 'number', 'required' => true],
                    ],
                ],
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
            'summaryEndpoint' => '/attendance/summary',
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
                ['key' => 'download_url', 'label' => 'File', 'link' => true],
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
            'actions' => [
                [
                    'key' => 'update_status', 'label' => 'Status', 'method' => 'PUT', 'endpoint' => '/tasks/{id}/status',
                    'permission' => 'task.update',
                    'fields' => [
                        [
                            'key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true,
                            'prefillFrom' => 'status',
                            'options' => [
                                ['value' => 'todo', 'label' => 'To do'],
                                ['value' => 'in_progress', 'label' => 'In progress'],
                                ['value' => 'in_review', 'label' => 'In review'],
                                ['value' => 'blocked', 'label' => 'Blocked'],
                                ['value' => 'done', 'label' => 'Done'],
                                ['value' => 'cancelled', 'label' => 'Cancelled'],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'update_assignee', 'label' => 'Assignee', 'method' => 'PUT',
                    'endpoint' => '/tasks/{id}/assignee', 'permission' => 'task.assign',
                    'fields' => [
                        ['key' => 'assignee_employee_id', 'label' => 'Employee', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'employees']],
                        ['key' => 'assignee_team_id', 'label' => 'Team', 'type' => 'relation', 'required' => false, 'relation' => ['resource' => 'teams']],
                    ],
                ],
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
            'detail' => [
                'fields' => [
                    ['key' => 'description', 'label' => 'Description'],
                    ['key' => 'project.name', 'label' => 'Project'],
                    ['key' => 'assignee_employee.full_name', 'label' => 'Assignee'],
                ],
                'relatedLists' => [
                    [
                        'key' => 'comments', 'label' => 'Comments', 'labelField' => 'body',
                        'listEndpoint' => '/tasks/{id}/comments', 'rowEndpoint' => '/task-comments/{id}',
                        'permissions' => ['view' => 'task.view', 'create' => 'task.view', 'update' => 'task.view', 'delete' => 'task.view'],
                        'ownerField' => 'author_id', 'bypassPermission' => 'task.view_all',
                        'columns' => [
                            ['key' => 'author_name', 'label' => 'Author'],
                            ['key' => 'body', 'label' => 'Comment'],
                            ['key' => 'created_at', 'label' => 'Posted'],
                        ],
                        'fields' => [
                            ['key' => 'body', 'label' => 'Comment', 'type' => 'text', 'required' => true],
                        ],
                    ],
                    [
                        'key' => 'attachments', 'label' => 'Attachments', 'labelField' => 'original_name',
                        'listEndpoint' => '/tasks/{id}/attachments', 'rowEndpoint' => '/task-attachments/{id}',
                        'permissions' => ['view' => 'task.view', 'create' => 'task.view', 'update' => null, 'delete' => 'task.view'],
                        'ownerField' => 'uploaded_by', 'bypassPermission' => 'task.view_all',
                        'columns' => [
                            ['key' => 'original_name', 'label' => 'File'],
                            ['key' => 'uploader_name', 'label' => 'Uploaded by'],
                            ['key' => 'download_url', 'label' => 'Download', 'link' => true],
                        ],
                        'fields' => [
                            ['key' => 'file', 'label' => 'File', 'type' => 'file', 'required' => true],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Contacts/follow-ups/activity are identical under leads and customers,
     * just with a different endpoint prefix and permission-string family —
     * shared here rather than duplicated in both resource methods.
     *
     * @return array<string, mixed>
     */
    private function crmContacts(string $parentSlug, string $viewPermission, string $updatePermission): array
    {
        return [
            'key' => 'contacts',
            'label' => 'Contacts',
            'labelField' => 'name',
            'listEndpoint' => "/{$parentSlug}/{id}/contacts",
            'rowEndpoint' => '/contacts/{id}',
            'permissions' => [
                'view' => $viewPermission, 'create' => $updatePermission,
                'update' => $updatePermission, 'delete' => $updatePermission,
            ],
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'is_primary', 'label' => 'Primary'],
            ],
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'string', 'required' => true],
                ['key' => 'title', 'label' => 'Title', 'type' => 'string', 'required' => false],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string', 'required' => false],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string', 'required' => false],
                ['key' => 'is_primary', 'label' => 'Primary contact', 'type' => 'boolean', 'required' => false],
                ['key' => 'notes', 'label' => 'Notes', 'type' => 'text', 'required' => false],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function crmFollowUps(string $parentSlug, string $viewPermission, string $updatePermission): array
    {
        return [
            'key' => 'follow_ups',
            'label' => 'Follow-ups',
            'labelField' => 'id',
            'listEndpoint' => "/{$parentSlug}/{id}/follow-ups",
            'rowEndpoint' => '/follow-ups/{id}',
            'permissions' => [
                'view' => $viewPermission, 'create' => $updatePermission,
                'update' => 'follow_up.update', 'delete' => 'follow_up.delete',
            ],
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
            'actions' => [
                [
                    'key' => 'complete', 'label' => 'Complete', 'method' => 'POST',
                    'endpoint' => '/follow-ups/{id}/complete', 'permission' => 'follow_up.update',
                    'fields' => [['key' => 'outcome', 'label' => 'Outcome', 'type' => 'text', 'required' => false]],
                ],
                [
                    'key' => 'cancel', 'label' => 'Cancel', 'method' => 'POST', 'endpoint' => '/follow-ups/{id}/cancel',
                    'permission' => 'follow_up.update', 'style' => 'destructive',
                    'confirm' => 'Cancel this follow-up?', 'fields' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function crmActivity(string $parentSlug, string $viewPermission, string $updatePermission): array
    {
        return [
            'label' => 'Activity',
            'listEndpoint' => "/{$parentSlug}/{id}/activities",
            'noteEndpoint' => "/{$parentSlug}/{id}/notes",
            'permission' => $viewPermission,
            'notePermission' => $updatePermission,
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
            'actions' => [
                [
                    'key' => 'move_stage', 'label' => 'Stage', 'method' => 'PUT', 'endpoint' => '/leads/{id}/stage',
                    'permission' => 'lead.update',
                    'fields' => [
                        [
                            'key' => 'stage', 'label' => 'Stage', 'type' => 'select', 'required' => true,
                            'prefillFrom' => 'stage',
                            'options' => [
                                ['value' => 'new', 'label' => 'New'],
                                ['value' => 'contacted', 'label' => 'Contacted'],
                                ['value' => 'interested', 'label' => 'Interested'],
                                ['value' => 'follow_up', 'label' => 'Follow up'],
                                ['value' => 'negotiation', 'label' => 'Negotiation'],
                                ['value' => 'lost', 'label' => 'Lost'],
                            ],
                        ],
                        ['key' => 'lost_reason', 'label' => 'Lost reason', 'type' => 'text', 'required' => false],
                    ],
                ],
                [
                    'key' => 'convert', 'label' => 'Convert', 'method' => 'POST', 'endpoint' => '/leads/{id}/convert',
                    'permission' => 'lead.convert', 'confirm' => 'Convert this lead to a customer?', 'fields' => [],
                ],
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
            'detail' => [
                'fields' => [
                    ['key' => 'company', 'label' => 'Company'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'estimated_value', 'label' => 'Estimated value'],
                    ['key' => 'notes', 'label' => 'Notes'],
                ],
                'relatedLists' => [
                    $this->crmContacts('leads', 'lead.view', 'lead.update'),
                    $this->crmFollowUps('leads', 'lead.view', 'lead.update'),
                ],
                'activity' => $this->crmActivity('leads', 'lead.view', 'lead.update'),
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
            'detail' => [
                'fields' => [
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'company', 'label' => 'Company'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'address', 'label' => 'Address'],
                ],
                'relatedLists' => [
                    $this->crmContacts('customers', 'customer.view', 'customer.update'),
                    $this->crmFollowUps('customers', 'customer.view', 'customer.update'),
                ],
                'activity' => $this->crmActivity('customers', 'customer.view', 'customer.update'),
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
            'actions' => [
                [
                    'key' => 'complete', 'label' => 'Complete', 'method' => 'POST',
                    'endpoint' => '/follow-ups/{id}/complete', 'permission' => 'follow_up.update',
                    'fields' => [['key' => 'outcome', 'label' => 'Outcome', 'type' => 'text', 'required' => false]],
                ],
                [
                    'key' => 'cancel', 'label' => 'Cancel', 'method' => 'POST', 'endpoint' => '/follow-ups/{id}/cancel',
                    'permission' => 'follow_up.update', 'style' => 'destructive',
                    'confirm' => 'Cancel this follow-up?', 'fields' => [],
                ],
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
            'actions' => [
                [
                    'key' => 'approve', 'label' => 'Approve', 'method' => 'POST', 'endpoint' => '/expenses/{id}/approve',
                    'permission' => 'expense.approve', 'confirm' => 'Approve this expense?',
                    'fields' => [['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false]],
                ],
                [
                    'key' => 'reject', 'label' => 'Reject', 'method' => 'POST', 'endpoint' => '/expenses/{id}/reject',
                    'permission' => 'expense.approve', 'style' => 'destructive',
                    'fields' => [['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false]],
                ],
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
            'actions' => [
                [
                    'key' => 'send', 'label' => 'Send', 'method' => 'POST', 'endpoint' => '/invoices/{id}/send',
                    'permission' => 'invoice.send', 'confirm' => 'Send this invoice to the customer?', 'fields' => [],
                ],
                [
                    'key' => 'void', 'label' => 'Void', 'method' => 'POST', 'endpoint' => '/invoices/{id}/void',
                    'permission' => 'invoice.void', 'style' => 'destructive',
                    'confirm' => 'Void this invoice? This cannot be undone.', 'fields' => [],
                ],
                [
                    'key' => 'record_payment', 'label' => 'Record payment', 'method' => 'POST',
                    'endpoint' => '/invoices/{id}/payments', 'permission' => 'invoice.record_payment',
                    'fields' => [
                        ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                        ['key' => 'paid_on', 'label' => 'Paid on', 'type' => 'date', 'required' => true],
                        [
                            'key' => 'method', 'label' => 'Method', 'type' => 'select', 'required' => false,
                            'options' => [
                                ['value' => 'cash', 'label' => 'Cash'],
                                ['value' => 'bank_transfer', 'label' => 'Bank transfer'],
                                ['value' => 'card', 'label' => 'Card'],
                                ['value' => 'mobile', 'label' => 'Mobile'],
                                ['value' => 'cheque', 'label' => 'Cheque'],
                                ['value' => 'other', 'label' => 'Other'],
                            ],
                        ],
                        ['key' => 'reference', 'label' => 'Reference', 'type' => 'string', 'required' => false],
                        ['key' => 'note', 'label' => 'Note', 'type' => 'text', 'required' => false],
                    ],
                ],
                [
                    'key' => 'refund', 'label' => 'Refund', 'method' => 'POST', 'endpoint' => '/invoices/{id}/refunds',
                    'permission' => 'invoice.refund', 'style' => 'destructive',
                    'fields' => [
                        ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                        ['key' => 'refunded_on', 'label' => 'Refunded on', 'type' => 'date', 'required' => true],
                        [
                            'key' => 'method', 'label' => 'Method', 'type' => 'select', 'required' => false,
                            'options' => [
                                ['value' => 'cash', 'label' => 'Cash'],
                                ['value' => 'bank_transfer', 'label' => 'Bank transfer'],
                                ['value' => 'card', 'label' => 'Card'],
                                ['value' => 'mobile', 'label' => 'Mobile'],
                                ['value' => 'cheque', 'label' => 'Cheque'],
                                ['value' => 'other', 'label' => 'Other'],
                            ],
                        ],
                        ['key' => 'reason', 'label' => 'Reason', 'type' => 'text', 'required' => false],
                    ],
                ],
            ],
            'fields' => [
                ['key' => 'customer_id', 'label' => 'Customer', 'type' => 'relation', 'required' => true, 'relation' => ['resource' => 'customers']],
                ['key' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'required' => true],
                ['key' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'required' => false],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true],
                ['key' => 'notes', 'label' => 'Notes', 'type' => 'text', 'required' => false],
            ],
            'detail' => [
                'fields' => [
                    ['key' => 'customer.name', 'label' => 'Customer'],
                    ['key' => 'issue_date', 'label' => 'Issue date'],
                    ['key' => 'due_date', 'label' => 'Due date'],
                    ['key' => 'amount', 'label' => 'Amount'],
                    ['key' => 'amount_paid', 'label' => 'Paid'],
                    ['key' => 'amount_refunded', 'label' => 'Refunded'],
                    ['key' => 'amount_due', 'label' => 'Due'],
                    ['key' => 'notes', 'label' => 'Notes'],
                ],
                // Payments/refunds are embedded in GET /invoices/{id} itself
                // (InvoiceController::show eager-loads both) rather than
                // their own list endpoint — refunds in particular has no
                // GET route at all, only POST. Read-only: they're created
                // via the record_payment/refund actions above, not a form
                // here.
                'embeddedLists' => [
                    [
                        'key' => 'payments', 'label' => 'Payments', 'path' => 'payments',
                        'columns' => [
                            ['key' => 'paid_on', 'label' => 'Paid on'],
                            ['key' => 'method', 'label' => 'Method'],
                            ['key' => 'amount', 'label' => 'Amount'],
                            ['key' => 'reference', 'label' => 'Reference'],
                        ],
                    ],
                    [
                        'key' => 'refunds', 'label' => 'Refunds', 'path' => 'refunds',
                        'columns' => [
                            ['key' => 'refunded_on', 'label' => 'Refunded on'],
                            ['key' => 'method', 'label' => 'Method'],
                            ['key' => 'amount', 'label' => 'Amount'],
                            ['key' => 'reason', 'label' => 'Reason'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
