<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;

/**
 * Application input for creating an installment plan (and its installments)
 * on a confirmed booking.
 */
final readonly class InstallmentPlanData
{
    public function __construct(
        public string $downPaymentAmount,
        public int $installmentCount,
        public PaymentPlanFrequency $frequency,
        public string $startDate,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            downPaymentAmount: (string) $validated['down_payment_amount'],
            installmentCount: (int) $validated['installment_count'],
            frequency: PaymentPlanFrequency::from((string) $validated['frequency']),
            startDate: (string) $validated['start_date'],
        );
    }
}
