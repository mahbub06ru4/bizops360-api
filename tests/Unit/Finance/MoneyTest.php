<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\Money;

it('builds from a decimal string, float or integer', function (): void {
    expect(Money::fromDecimal('1234.50')->minorUnits)->toBe(123450)
        ->and(Money::fromDecimal(1234.5)->minorUnits)->toBe(123450)
        ->and(Money::fromDecimal(1234)->minorUnits)->toBe(123400);
});

it('rounds half up to the cent and absorbs binary float error', function (): void {
    expect(Money::fromDecimal(0.1)->add(Money::fromDecimal(0.2))->toDecimalString())->toBe('0.30')
        ->and(Money::fromDecimal('19.99')->minorUnits)->toBe(1999)
        ->and(Money::fromDecimal('0.125')->toDecimalString())->toBe('0.13');
});

it('adds and subtracts without drift', function (): void {
    $total = Money::zero();
    foreach (range(1, 10) as $ignored) {
        $total = $total->add(Money::fromDecimal('0.10'));
    }

    expect($total->toDecimalString())->toBe('1.00');
});

it('reports sign and can clamp negatives to zero', function (): void {
    $negative = Money::fromDecimal('5.00')->subtract(Money::fromDecimal('8.00'));

    expect($negative->isNegative())->toBeTrue()
        ->and($negative->toDecimalString())->toBe('-3.00')
        ->and($negative->clampToZero()->toDecimalString())->toBe('0.00')
        ->and(Money::zero()->isZero())->toBeTrue();
});

it('compares two amounts', function (): void {
    expect(Money::fromDecimal('10.00')->greaterThan(Money::fromDecimal('9.99')))->toBeTrue()
        ->and(Money::fromDecimal('10.00')->equals(Money::fromDecimal('10.00')))->toBeTrue()
        ->and(Money::fromDecimal('10.00')->lessThan(Money::fromDecimal('10.01')))->toBeTrue();
});

it('rejects a non-numeric amount', function (): void {
    Money::fromDecimal('not-money');
})->throws(InvalidArgumentException::class);
