<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

/**
 * Application input for setting an employee's leave entitlement for a year.
 */
final readonly class LeaveBalanceData
{
    public function __construct(
        public int $employeeId,
        public int $leaveTypeId,
        public int $year,
        public int $entitledDays,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: (int) $validated['employee_id'],
            leaveTypeId: (int) $validated['leave_type_id'],
            year: (int) $validated['year'],
            entitledDays: (int) $validated['entitled_days'],
        );
    }
}
