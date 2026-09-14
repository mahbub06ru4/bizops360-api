<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

use App\Modules\Industry\RealEstate\Models\PropertyMatch;

/**
 * Lifecycle of a {@see PropertyMatch}
 * suggestion as the sales team and (eventually) the buyer work through it.
 */
enum PropertyMatchStatus: string
{
    case Suggested = 'suggested';
    case Viewed = 'viewed';
    case Interested = 'interested';
    case Rejected = 'rejected';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
