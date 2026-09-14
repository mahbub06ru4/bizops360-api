<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum SiteVisitStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isOpen(): bool
    {
        return $this === self::Scheduled;
    }
}
