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
                    'resources' => ['branches', 'departments', 'designations', 'employees'],
                ],
            ],
            'resources' => [
                $this->branches(),
                $this->departments(),
                $this->designations(),
                $this->employees(),
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
}
