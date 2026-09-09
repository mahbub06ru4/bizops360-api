<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Finance\Models\Expense;

/**
 * Application input for creating or updating an {@see Expense}.
 */
final readonly class ExpenseData
{
    public function __construct(
        public ExpenseCategory $category,
        public ?int $employeeId,
        public ?string $supplierName,
        public string $title,
        public string $amount,
        public string $spentOn,
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
            category: ExpenseCategory::from((string) ($validated['category'] ?? ExpenseCategory::Other->value)),
            employeeId: isset($validated['employee_id']) ? (int) $validated['employee_id'] : null,
            supplierName: isset($validated['supplier_name']) ? (string) $validated['supplier_name'] : null,
            title: (string) $validated['title'],
            amount: Money::fromDecimal((string) $validated['amount'])->toDecimalString(),
            spentOn: (string) $validated['spent_on'],
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
            'category' => $this->category,
            'employee_id' => $this->employeeId,
            'supplier_name' => $this->supplierName,
            'title' => $this->title,
            'amount' => $this->amount,
            'spent_on' => $this->spentOn,
            'method' => $this->method,
            'reference' => $this->reference,
            'note' => $this->note,
        ];
    }
}
