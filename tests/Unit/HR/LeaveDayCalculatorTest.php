<?php

declare(strict_types=1);

use App\Modules\HR\Domain\LeaveDayCalculator;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->calculator = new LeaveDayCalculator;
});

it('counts every calendar day in an inclusive range', function (): void {
    expect($this->calculator->countChargeableDays(
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-05'),
        [],
    ))->toBe(5);
});

it('excludes holidays that fall inside the range', function (): void {
    expect($this->calculator->countChargeableDays(
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-05'),
        ['2026-06-03', '2026-06-04', '2026-07-01'],
    ))->toBe(3);
});

it('treats a single day as one chargeable day', function (): void {
    expect($this->calculator->countChargeableDays(
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-01'),
        [],
    ))->toBe(1);
});

it('returns zero when the range is inverted', function (): void {
    expect($this->calculator->countChargeableDays(
        Carbon::parse('2026-06-05'),
        Carbon::parse('2026-06-01'),
        [],
    ))->toBe(0);
});
