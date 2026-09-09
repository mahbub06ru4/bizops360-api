<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\Travel\Domain\BookingProfit;

function profit(string $sell, string $cost, string $commission, string $refund = '0.00'): BookingProfit
{
    return new BookingProfit(
        Money::fromDecimal($sell),
        Money::fromDecimal($cost),
        Money::fromDecimal($commission),
        Money::fromDecimal($refund),
    );
}

it('computes margin, gross and net profit', function (): void {
    $p = profit('90000.00', '82000.00', '2500.00');

    expect($p->margin()->toDecimalString())->toBe('8000.00')
        ->and($p->gross()->toDecimalString())->toBe('10500.00')
        ->and($p->net()->toDecimalString())->toBe('10500.00');
});

it('subtracts a refund from the net', function (): void {
    $p = profit('90000.00', '82000.00', '2500.00', '15000.00');

    expect($p->gross()->toDecimalString())->toBe('10500.00')
        ->and($p->net()->toDecimalString())->toBe('-4500.00');
});

it('handles fractional amounts without drift', function (): void {
    $p = profit('100.10', '33.37', '0.03');

    expect($p->margin()->toDecimalString())->toBe('66.73')
        ->and($p->net()->toDecimalString())->toBe('66.76');
});

it('serialises to a stable shape', function (): void {
    expect(profit('500.00', '400.00', '25.00', '10.00')->toArray())->toBe([
        'sell' => '500.00',
        'cost' => '400.00',
        'commission' => '25.00',
        'refund' => '10.00',
        'margin' => '100.00',
        'gross_profit' => '125.00',
        'net_profit' => '115.00',
    ]);
});
