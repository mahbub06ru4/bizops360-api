<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\PaymentMethod;

/**
 * Application input for refunding money against an invoice.
 */
final readonly class InvoiceRefundData
{
    public function __construct(
        public ?int $paymentId,
        public string $amount,
        public string $refundedOn,
        public PaymentMethod $method,
        public ?string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            paymentId: isset($validated['payment_id']) ? (int) $validated['payment_id'] : null,
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            refundedOn: (string) $validated['refunded_on'],
            method: PaymentMethod::from((string) ($validated['method'] ?? PaymentMethod::Cash->value)),
            reason: isset($validated['reason']) ? (string) $validated['reason'] : null,
        );
    }
}
