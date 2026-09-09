<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Income;

/**
 * Application input for creating or updating an {@see Income}.
 */
final readonly class IncomeData
{
    public function __construct(
        public ?int $customerId,
        public IncomeCategory $category,
        public ?string $source,
        public string $amount,
        public string $receivedOn,
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
            customerId: isset($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            category: IncomeCategory::from((string) ($validated['category'] ?? IncomeCategory::Other->value)),
            source: isset($validated['source']) ? (string) $validated['source'] : null,
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            receivedOn: (string) $validated['received_on'],
            method: PaymentMethod::from((string) ($validated['method'] ?? PaymentMethod::Cash->value)),
            reference: isset($validated['reference']) ? (string) $validated['reference'] : null,
            note: isset($validated['note']) ? (string) $validated['note'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'customer_id' => $this->customerId,
            'category' => $this->category,
            'source' => $this->source,
            'amount' => $this->amount,
            'received_on' => $this->receivedOn,
            'method' => $this->method,
            'reference' => $this->reference,
            'note' => $this->note,
        ];
    }
}
