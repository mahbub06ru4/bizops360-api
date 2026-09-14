<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum PaymentPlanFrequency: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
