<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;

/**
 * Application input for adding an installment plan template to a project.
 */
final readonly class PaymentPlanData
{
    public function __construct(
        public string $name,
        public string $downPaymentPercent,
        public int $installmentCount,
        public PaymentPlanFrequency $installmentFrequency,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            downPaymentPercent: (string) $validated['down_payment_percent'],
            installmentCount: (int) $validated['installment_count'],
            installmentFrequency: PaymentPlanFrequency::from((string) ($validated['installment_frequency'] ?? PaymentPlanFrequency::Monthly->value)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'down_payment_percent' => $this->downPaymentPercent,
            'installment_count' => $this->installmentCount,
            'installment_frequency' => $this->installmentFrequency,
        ];
    }
}
