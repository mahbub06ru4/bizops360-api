<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

use App\Modules\Industry\RealEstate\Models\Offer;

/**
 * Who initiated an {@see Offer} in the
 * negotiation chain. Phase 1 has no buyer-side authentication yet, so this is
 * a record of intent captured by tenant staff on the lead's behalf — not an
 * access-control distinction.
 */
enum OfferedBy: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
