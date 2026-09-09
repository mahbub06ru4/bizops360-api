<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Domain\ProfitAndLoss;

it('computes profit as income minus expense', function (): void {
    $pl = new ProfitAndLoss(Money::fromDecimal('10000.00'), Money::fromDecimal('6500.00'));

    expect($pl->profit()->toDecimalString())->toBe('3500.00');
});

it('reports a loss as a negative profit', function (): void {
    $pl = new ProfitAndLoss(Money::fromDecimal('4000.00'), Money::fromDecimal('9000.00'));

    expect($pl->profit()->toDecimalString())->toBe('-5000.00')
        ->and($pl->margin())->toBe(-125.0);
});

it('rounds margin to one decimal place', function (): void {
    $pl = new ProfitAndLoss(Money::fromDecimal('3000.00'), Money::fromDecimal('1000.00'));

    expect($pl->margin())->toBe(66.7);
});

it('returns a null margin when there is no income', function (): void {
    $pl = new ProfitAndLoss(Money::zero(), Money::fromDecimal('500.00'));

    expect($pl->margin())->toBeNull()
        ->and($pl->profit()->toDecimalString())->toBe('-500.00');
});

it('serialises to a stable array shape', function (): void {
    $pl = new ProfitAndLoss(Money::fromDecimal('1000.00'), Money::fromDecimal('250.00'));

    expect($pl->toArray())->toBe([
        'income' => '1000.00',
        'expense' => '250.00',
        'profit' => '750.00',
        'margin' => 75.0,
    ]);
});
