<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Organization\Models\Employee;

/**
 * Application input for creating or updating an {@see Employee}.
 */
final readonly class EmployeeData
{
    public function __construct(
        public ?int $userId,
        public ?int $branchId,
        public ?int $departmentId,
        public ?int $designationId,
        public string $employeeCode,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $phone,
        public string $hireDate,
        public EmploymentStatus $employmentStatus,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            userId: isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            departmentId: isset($validated['department_id']) ? (int) $validated['department_id'] : null,
            designationId: isset($validated['designation_id']) ? (int) $validated['designation_id'] : null,
            employeeCode: (string) $validated['employee_code'],
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            hireDate: (string) $validated['hire_date'],
            employmentStatus: EmploymentStatus::from((string) ($validated['employment_status'] ?? EmploymentStatus::Active->value)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'branch_id' => $this->branchId,
            'department_id' => $this->departmentId,
            'designation_id' => $this->designationId,
            'employee_code' => $this->employeeCode,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'hire_date' => $this->hireDate,
            'employment_status' => $this->employmentStatus,
        ];
    }
}
