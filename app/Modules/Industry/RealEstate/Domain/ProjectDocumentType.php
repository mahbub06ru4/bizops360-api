<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

enum ProjectDocumentType: string
{
    case RajukApproval = 'rajuk_approval';
    case LandDeed = 'land_deed';
    case Mutation = 'mutation';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
