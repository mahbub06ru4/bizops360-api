<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

/**
 * The approval lifecycle state of a recorded expense.
 */
enum ExpenseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
