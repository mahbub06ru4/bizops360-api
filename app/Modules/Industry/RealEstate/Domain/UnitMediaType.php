<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum UnitMediaType: string
{
    case Image = 'image';
    case FloorPlan = 'floor_plan';
    case Video = 'video';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
