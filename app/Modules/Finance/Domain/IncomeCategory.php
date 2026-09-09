<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

enum IncomeCategory: string
{
    case CustomerPayment = 'customer_payment';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
