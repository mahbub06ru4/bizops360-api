<?php

declare(strict_types=1);

use App\Modules\Organization\Domain\EmploymentStatus;

it('exposes its values as a plain list', function (): void {
    expect(EmploymentStatus::values())
        ->toBe(['active', 'probation', 'on_leave', 'terminated']);
});

it('knows when it represents a terminated employee', function (): void {
    expect(EmploymentStatus::Terminated->isTerminated())->toBeTrue()
        ->and(EmploymentStatus::Active->isTerminated())->toBeFalse();
});
