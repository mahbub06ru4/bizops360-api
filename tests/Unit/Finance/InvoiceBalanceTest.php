<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\InvoiceBalance;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Domain\Money;

function balance(string $total, string $paid, string $refunded): InvoiceBalance
{
    return new InvoiceBalance(
        Money::fromDecimal($total),
        Money::fromDecimal($paid),
        Money::fromDecimal($refunded),
    );
}

it('computes the amount still due, never negative', function (): void {
    expect(balance('1000.00', '0.00', '0.00')->due()->toDecimalString())->toBe('1000.00')
        ->and(balance('1000.00', '400.00', '0.00')->due()->toDecimalString())->toBe('600.00')
        ->and(balance('1000.00', '1000.00', '0.00')->due()->toDecimalString())->toBe('0.00')
        ->and(balance('1000.00', '1200.00', '0.00')->due()->toDecimalString())->toBe('0.00');
});

it('nets refunds against payments', function (): void {
    $b = balance('1000.00', '1000.00', '400.00');

    expect($b->netPaid()->toDecimalString())->toBe('600.00')
        ->and($b->due()->toDecimalString())->toBe('400.00');
});

it('derives a draft invoice status from its balance', function (): void {
    expect(balance('1000.00', '0.00', '0.00')->statusFrom(InvoiceStatus::Draft))->toBe(InvoiceStatus::Draft)
        ->and(balance('1000.00', '0.00', '0.00')->statusFrom(InvoiceStatus::Sent))->toBe(InvoiceStatus::Sent)
        ->and(balance('1000.00', '250.00', '0.00')->statusFrom(InvoiceStatus::Sent))->toBe(InvoiceStatus::Partial)
        ->and(balance('1000.00', '1000.00', '0.00')->statusFrom(InvoiceStatus::Sent))->toBe(InvoiceStatus::Paid);
});

it('marks an invoice refunded once payments are fully returned', function (): void {
    expect(balance('1000.00', '1000.00', '1000.00')->statusFrom(InvoiceStatus::Paid))->toBe(InvoiceStatus::Refunded)
        ->and(balance('1000.00', '1000.00', '400.00')->statusFrom(InvoiceStatus::Paid))->toBe(InvoiceStatus::Partial);
});

it('keeps a voided invoice voided regardless of balance', function (): void {
    expect(balance('1000.00', '1000.00', '0.00')->statusFrom(InvoiceStatus::Void))->toBe(InvoiceStatus::Void);
});

it('treats a fully-paid invoice at the cent boundary as paid', function (): void {
    $b = balance('99.99', '33.33', '0.00')->statusFrom(InvoiceStatus::Sent);
    expect($b)->toBe(InvoiceStatus::Partial);

    $paid = balance('99.99', '99.99', '0.00');
    expect($paid->due()->toDecimalString())->toBe('0.00')
        ->and($paid->statusFrom(InvoiceStatus::Sent))->toBe(InvoiceStatus::Paid);
});
