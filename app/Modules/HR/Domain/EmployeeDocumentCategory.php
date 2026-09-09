<?php

declare(strict_types=1);

namespace App\Modules\HR\Domain;

/**
 * The kind of an employee document.
 */
enum EmployeeDocumentCategory: string
{
    case Nid = 'nid';
    case Passport = 'passport';
    case Contract = 'contract';
    case OfferLetter = 'offer_letter';
    case Certificate = 'certificate';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
