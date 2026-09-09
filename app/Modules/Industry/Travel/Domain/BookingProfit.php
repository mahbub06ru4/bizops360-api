<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

use App\Modules\Finance\Domain\Money;

/**
 * What the agency actually earns on a booking:
 *
 *   margin      = sell − cost
 *   gross       = margin + commission        (commission/incentive from the airline
 *                                             or consolidator, on top of the margin)
 *   net         = gross − refund             (money handed back to the customer)
 */
final readonly class BookingProfit
{
    public function __construct(
        public Money $sell,
        public Money $cost,
        public Money $commission,
        public Money $refund,
    ) {}

    public function margin(): Money
    {
        return $this->sell->subtract($this->cost);
    }

    public function gross(): Money
    {
        return $this->margin()->add($this->commission);
    }

    public function net(): Money
    {
        return $this->gross()->subtract($this->refund);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'sell' => $this->sell->toDecimalString(),
            'cost' => $this->cost->toDecimalString(),
            'commission' => $this->commission->toDecimalString(),
            'refund' => $this->refund->toDecimalString(),
            'margin' => $this->margin()->toDecimalString(),
            'gross_profit' => $this->gross()->toDecimalString(),
            'net_profit' => $this->net()->toDecimalString(),
        ];
    }
}
