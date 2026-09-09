<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain;

enum FollowUpType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Task = 'task';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
