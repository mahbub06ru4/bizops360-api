<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Finance\Domain\Money;

/**
 * Application input for refunding a booking.
 */
final readonly class RefundBookingData
{
    public function __construct(
        public string $amount,
        public string $refundedOn,
        public ?string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            refundedOn: (string) $validated['refunded_on'],
            reason: isset($validated['reason']) ? (string) $validated['reason'] : null,
        );
    }
}
