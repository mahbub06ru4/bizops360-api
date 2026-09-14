<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

/**
 * How a {@see \App\Modules\Industry\RealEstate\Models\RealEstateProject} is
 * structured commercially. `LandShare` projects carry {@see \App\Modules\Industry\RealEstate\Models\LandShare}
 * and {@see \App\Modules\Industry\RealEstate\Models\LandRecord} rows in addition
 * to the usual building/unit inventory.
 */
enum ProjectType: string
{
    case Apartment = 'apartment';
    case LandShare = 'land_share';
    case Commercial = 'commercial';
    case Plot = 'plot';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
