<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

use App\Modules\Industry\RealEstate\Models\Unit;

/**
 * Sales state of a single {@see Unit}.
 * Phase 1 (bookings/installments) will drive this transition; Phase 0 only
 * needs the column to exist and default sensibly.
 */
enum UnitStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
