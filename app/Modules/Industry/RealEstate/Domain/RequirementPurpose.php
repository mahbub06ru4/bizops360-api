<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

/**
 * Why a lead is looking — drives how a {@see \App\Modules\Industry\RealEstate\Actions\MatchRequirementToUnits}
 * scorer and the sales team read the requirement; Phase 1 does not otherwise
 * change behaviour by purpose.
 */
enum RequirementPurpose: string
{
    case Buy = 'buy';
    case Invest = 'invest';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
