<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

/**
 * Lifecycle of a booking (an air ticket, package, hotel stay, …).
 *
 * quoted → confirmed → ticketed → completed
 *                    ↘ cancelled ↘ refunded
 */
enum BookingStatus: string
{
    case Quoted = 'quoted';
    case Confirmed = 'confirmed';
    case Ticketed = 'ticketed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isEditable(): bool
    {
        return $this === self::Quoted || $this === self::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled || $this === self::Refunded;
    }

    /**
     * Whether the booking still counts as live business (owes an invoice, will
     * travel).
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::Quoted, self::Confirmed, self::Ticketed, self::Completed => true,
            self::Cancelled, self::Refunded => false,
        };
    }
}
