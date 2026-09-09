<?php

declare(strict_types=1);

namespace App\Modules\HR\Domain;

/**
 * The recorded state of an employee's attendance on a given day.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case HalfDay = 'half_day';
    case OnLeave = 'on_leave';
    case Holiday = 'holiday';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Statuses that count as the employee having worked (fully or partly).
     */
    public function isWorked(): bool
    {
        return match ($this) {
            self::Present, self::Late, self::HalfDay => true,
            default => false,
        };
    }
}
