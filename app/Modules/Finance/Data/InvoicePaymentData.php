<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\PaymentMethod;

/**
 * Application input for recording a payment against an invoice.
 */
final readonly class InvoicePaymentData
{
    public function __construct(
        public string $amount,
        public string $paidOn,
        public PaymentMethod $method,
        public ?string $reference,
        public ?string $note,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            paidOn: (string) $validated['paid_on'],
            method: PaymentMethod::from((string) ($validated['method'] ?? PaymentMethod::Cash->value)),
            reference: isset($validated['reference']) ? (string) $validated['reference'] : null,
            note: isset($validated['note']) ? (string) $validated['note'] : null,
        );
    }
}
