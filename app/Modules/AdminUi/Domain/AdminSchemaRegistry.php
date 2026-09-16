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
 * boolean, date, select (static `options`), relation (`relation.resource`
 * points at another resource key in this same registry, fetched as a
 * dropdown of {id, label}).
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
                    'resources' => ['branches', 'departments', 'designations', 'employees', 'teams'],
                ],
                [
                    'key' => 'hr',
                    'label' => 'HR',
                    'icon' => 'Users',
                    'resources' => ['holidays', 'leave_types'],
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
                    'resources' => ['leads', 'customers'],
                ],
                [
                    'key' => 'finance',
                    'label' => 'Finance',
                    'icon' => 'Wallet',
                    'resources' => ['incomes', 'expenses', 'invoices'],
                ],
            ],
            'resources' => [
                $this->branches(),
                $this->departments(),
                $this->designations(),
                $this->employees(),
                $this->teams(),
                $this->holidays(),
                $this->leaveTypes(),
                $this->projects(),
                $this->tasks(),
                $this->leads(),
                $this->customers(),
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
