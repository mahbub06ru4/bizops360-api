<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

use App\Modules\Industry\RealEstate\Actions\CounterOffer;
use App\Modules\Industry\RealEstate\Models\Offer;

/**
 * State of one row in an {@see Offer}
 * negotiation chain. A countered or accepted/rejected offer is never mutated
 * again — {@see CounterOffer} always
 * creates a new row pointing back at it via `previous_offer_id`.
 */
enum OfferStatus: string
{
    case Pending = 'pending';
    case Countered = 'countered';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isOpen(): bool
    {
        return $this === self::Pending;
    }
}
