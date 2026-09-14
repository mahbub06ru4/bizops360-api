<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Finance\Domain\Money;

/**
 * Application input for setting a project's cost breakdown. `estimated_total`
 * is never taken from the client — {@see \App\Modules\Industry\RealEstate\Actions\SetProjectPricing}
 * computes it from the three cost lines.
 */
final readonly class ProjectPricingData
{
    public function __construct(
        public string $landCost,
        public string $constructionCost,
        public string $consultancyCost,
        public string $currency,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            landCost: Money::fromDecimal((string) ($validated['land_cost'] ?? '0'))->toDecimalString(),
            constructionCost: Money::fromDecimal((string) ($validated['construction_cost'] ?? '0'))->toDecimalString(),
            consultancyCost: Money::fromDecimal((string) ($validated['consultancy_cost'] ?? '0'))->toDecimalString(),
            currency: strtoupper((string) ($validated['currency'] ?? 'BDT')),
        );
    }
}
