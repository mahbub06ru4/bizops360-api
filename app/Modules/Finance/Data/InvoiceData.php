<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;

/**
 * Application input for creating or updating an {@see Invoice}. Payments and
 * refunds are separate use cases with their own DTOs.
 */
final readonly class InvoiceData
{
    public function __construct(
        public ?int $customerId,
        public string $issueDate,
        public ?string $dueDate,
        public string $amount,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            customerId: isset($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            issueDate: (string) $validated['issue_date'],
            dueDate: isset($validated['due_date']) ? (string) $validated['due_date'] : null,
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'customer_id' => $this->customerId,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'amount' => $this->amount,
            'notes' => $this->notes,
        ];
    }
}
