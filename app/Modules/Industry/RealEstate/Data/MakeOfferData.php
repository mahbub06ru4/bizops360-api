<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\OfferedBy;

/**
 * Application input for the first offer in a negotiation chain.
 */
final readonly class MakeOfferData
{
    public function __construct(
        public int $unitId,
        public string $offeredPrice,
        public OfferedBy $offeredBy,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            unitId: (int) $validated['unit_id'],
            offeredPrice: (string) $validated['offered_price'],
            offeredBy: OfferedBy::from((string) ($validated['offered_by'] ?? OfferedBy::Buyer->value)),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }
}
