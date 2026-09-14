<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum UnitFacing: string
{
    case North = 'north';
    case South = 'south';
    case East = 'east';
    case West = 'west';
    case Northeast = 'northeast';
    case Northwest = 'northwest';
    case Southeast = 'southeast';
    case Southwest = 'southwest';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
