<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

use App\Modules\HR\Domain\AttendanceStatus;

/**
 * Application input for a manager recording or overriding an attendance entry.
 */
final readonly class RecordAttendanceData
{
    public function __construct(
        public int $employeeId,
        public string $date,
        public AttendanceStatus $status,
        public ?string $checkInAt,
        public ?string $checkOutAt,
        public ?string $note,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: (int) $validated['employee_id'],
            date: (string) $validated['date'],
            status: AttendanceStatus::from((string) $validated['status']),
            checkInAt: isset($validated['check_in_at']) ? (string) $validated['check_in_at'] : null,
            checkOutAt: isset($validated['check_out_at']) ? (string) $validated['check_out_at'] : null,
            note: isset($validated['note']) ? (string) $validated['note'] : null,
        );
    }
}
