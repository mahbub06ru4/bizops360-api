<?php

declare(strict_types=1);

use App\Modules\HR\Domain\WorkSchedule;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->schedule = new WorkSchedule('09:00:00', '17:00:00', 15);
});

it('does not flag a check-in within the grace window', function (): void {
    expect($this->schedule->isLate(Carbon::parse('2026-06-01 09:14:00')))->toBeFalse();
});

it('flags a check-in past the grace window', function (): void {
    expect($this->schedule->isLate(Carbon::parse('2026-06-01 09:16:00')))->toBeTrue();
});

it('flags an early check-out', function (): void {
    expect($this->schedule->isEarlyLeave(Carbon::parse('2026-06-01 16:59:00')))->toBeTrue()
        ->and($this->schedule->isEarlyLeave(Carbon::parse('2026-06-01 17:00:00')))->toBeFalse();
});

it('counts worked minutes and clamps an inverted range to zero', function (): void {
    expect($this->schedule->workedMinutes(
        Carbon::parse('2026-06-01 09:00:00'),
        Carbon::parse('2026-06-01 17:30:00'),
    ))->toBe(510)
        ->and($this->schedule->workedMinutes(
            Carbon::parse('2026-06-01 17:00:00'),
            Carbon::parse('2026-06-01 09:00:00'),
        ))->toBe(0);
});

it('exposes platform defaults', function (): void {
    $default = WorkSchedule::default();

    expect($default->startsAt)->toBe('09:00:00')
        ->and($default->endsAt)->toBe('17:00:00')
        ->and($default->graceMinutes)->toBe(15);
});
