<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

use App\Modules\Industry\RealEstate\Models\RealEstateBooking;

/**
 * Lifecycle of a {@see RealEstateBooking}
 * — buying a unit, not a Travel-style trip booking (kept in its own table and
 * enum on purpose; see the model docblock).
 */
enum BookingStatus: string
{
    case Reserved = 'reserved';
    case Booked = 'booked';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
