<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

/**
 * Profit-and-loss arithmetic over a reporting period.
 */
final readonly class ProfitAndLoss
{
    public function __construct(
        public Money $income,
        public Money $expense,
    ) {}

    public function profit(): Money
    {
        return $this->income->subtract($this->expense);
    }

    /**
     * Net margin as a percentage of income, rounded to one decimal. Null when
     * there is no income to divide by.
     */
    public function margin(): ?float
    {
        if (! $this->income->isPositive()) {
            return null;
        }

        return round($this->profit()->toFloat() / $this->income->toFloat() * 100, 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'income' => $this->income->toDecimalString(),
            'expense' => $this->expense->toDecimalString(),
            'profit' => $this->profit()->toDecimalString(),
            'margin' => $this->margin(),
        ];
    }
}
