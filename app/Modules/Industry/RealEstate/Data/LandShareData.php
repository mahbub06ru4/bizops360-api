<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Finance\Domain\Money;

/**
 * Application input for a land-share project's share structure.
 */
final readonly class LandShareData
{
    public function __construct(
        public int $totalShares,
        public string $shareValue,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            totalShares: (int) $validated['total_shares'],
            shareValue: Money::fromDecimal((string) $validated['share_value'])->toDecimalString(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'total_shares' => $this->totalShares,
            'share_value' => $this->shareValue,
        ];
    }
}
