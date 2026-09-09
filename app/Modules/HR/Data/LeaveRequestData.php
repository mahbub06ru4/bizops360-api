<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

/**
 * Application input for an employee requesting leave. `employeeId` is null when a
 * user requests leave for themselves — the action resolves it from their linked
 * employee record.
 */
final readonly class LeaveRequestData
{
    public function __construct(
        public ?int $employeeId,
        public int $leaveTypeId,
        public string $startDate,
        public string $endDate,
        public ?string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: isset($validated['employee_id']) ? (int) $validated['employee_id'] : null,
            leaveTypeId: (int) $validated['leave_type_id'],
            startDate: (string) $validated['start_date'],
            endDate: (string) $validated['end_date'],
            reason: isset($validated['reason']) ? (string) $validated['reason'] : null,
        );
    }
}
