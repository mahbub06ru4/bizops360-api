<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\OfferedBy;

/**
 * Application input for a counter-offer against an existing one.
 */
final readonly class CounterOfferData
{
    public function __construct(
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
            offeredPrice: (string) $validated['offered_price'],
            offeredBy: OfferedBy::from((string) ($validated['offered_by'] ?? OfferedBy::Seller->value)),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }
}
