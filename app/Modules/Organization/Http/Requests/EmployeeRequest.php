<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Data\EmployeeData;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof Employee ? $employee->getKey() : null;
        $tenantId = $this->user()?->tenant_id;

        $scoped = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        return [
            'user_id' => [
                'nullable', 'integer',
                $scoped('users'),
                Rule::unique('employees', 'user_id')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNotNull('user_id'))
                    ->ignore($employeeId),
            ],
            'branch_id' => ['nullable', 'integer', $scoped('branches')],
            'department_id' => ['nullable', 'integer', $scoped('departments')],
            'designation_id' => ['nullable', 'integer', $scoped('designations')],
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('employees', 'employee_code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($employeeId),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('employees', 'email')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNotNull('email'))
                    ->ignore($employeeId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['sometimes', Rule::in(EmploymentStatus::values())],
        ];
    }

    public function toData(): EmployeeData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return EmployeeData::fromArray($validated);
    }
}
