<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

enum BookingType: string
{
    case AirTicket = 'air_ticket';
    case TourPackage = 'tour_package';
    case Hotel = 'hotel';
    case Transport = 'transport';
    case Insurance = 'insurance';
    case Umrah = 'umrah';
    case Hajj = 'hajj';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Air tickets require a PNR before they can be issued.
     */
    public function requiresPnr(): bool
    {
        return $this === self::AirTicket;
    }

    /**
     * Types that carry a day-by-day itinerary.
     */
    public function supportsItinerary(): bool
    {
        return $this === self::TourPackage || $this === self::Umrah || $this === self::Hajj;
    }
}
