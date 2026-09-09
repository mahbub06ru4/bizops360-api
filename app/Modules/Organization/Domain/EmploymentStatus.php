<?php

declare(strict_types=1);

namespace App\Modules\Organization\Domain;

/**
 * The lifecycle state of an employee within a tenant.
 */
enum EmploymentStatus: string
{
    case Active = 'active';
    case Probation = 'probation';
    case OnLeave = 'on_leave';
    case Terminated = 'terminated';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isTerminated(): bool
    {
        return $this === self::Terminated;
    }
}
