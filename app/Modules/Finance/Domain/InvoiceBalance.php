<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

/**
 * Pure balance arithmetic for a single invoice: what has been paid net of
 * refunds, what is still due, and the status the invoice should carry.
 */
final readonly class InvoiceBalance
{
    public function __construct(
        public Money $total,
        public Money $paid,
        public Money $refunded,
    ) {}

    /**
     * Payments received less anything refunded. Can be negative only through bad
     * data; callers guard against over-refunding.
     */
    public function netPaid(): Money
    {
        return $this->paid->subtract($this->refunded);
    }

    /**
     * Amount still owed by the customer, never negative.
     */
    public function due(): Money
    {
        return $this->total->subtract($this->netPaid())->clampToZero();
    }

    public function isFullyPaid(): bool
    {
        return ! $this->total->isZero() && ! $this->netPaid()->lessThan($this->total);
    }

    public function isPartiallyPaid(): bool
    {
        return $this->netPaid()->isPositive() && ! $this->isFullyPaid();
    }

    public function isFullyRefunded(): bool
    {
        return $this->refunded->isPositive() && ! $this->netPaid()->isPositive();
    }

    /**
     * The status the invoice should now carry, given the status it holds today.
     * A voided invoice stays void; an un-paid invoice keeps its draft/sent state.
     */
    public function statusFrom(InvoiceStatus $current): InvoiceStatus
    {
        if ($current === InvoiceStatus::Void) {
            return InvoiceStatus::Void;
        }

        if ($this->isFullyRefunded()) {
            return InvoiceStatus::Refunded;
        }

        if ($this->isFullyPaid()) {
            return InvoiceStatus::Paid;
        }

        if ($this->isPartiallyPaid()) {
            return InvoiceStatus::Partial;
        }

        return $current === InvoiceStatus::Draft ? InvoiceStatus::Draft : InvoiceStatus::Sent;
    }
}
