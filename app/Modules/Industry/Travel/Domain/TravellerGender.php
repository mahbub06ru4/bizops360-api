<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

enum TravellerGender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
