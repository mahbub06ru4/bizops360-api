<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain;

enum FollowUpStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

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
