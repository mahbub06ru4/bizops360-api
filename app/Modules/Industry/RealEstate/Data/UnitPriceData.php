<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\RealEstate\Domain\UnitPriceType;

/**
 * Application input for recording a price point on a unit.
 */
final readonly class UnitPriceData
{
    public function __construct(
        public string $price,
        public UnitPriceType $priceType,
        public ?string $effectiveFrom,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            price: Money::fromDecimal((string) $validated['price'])->toDecimalString(),
            priceType: UnitPriceType::from((string) ($validated['price_type'] ?? UnitPriceType::Base->value)),
            effectiveFrom: isset($validated['effective_from']) ? (string) $validated['effective_from'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'price' => $this->price,
            'price_type' => $this->priceType,
            'effective_from' => $this->effectiveFrom,
        ];
    }
}
