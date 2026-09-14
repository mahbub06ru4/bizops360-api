<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum UnitPriceType: string
{
    case Base = 'base';
    case Current = 'current';
    case PerSqft = 'per_sqft';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
