<?php

declare(strict_types=1);

namespace App\Modules\HR\Domain;

use Illuminate\Support\Carbon;

/**
 * A tenant's working-hours policy. Pure value object — decides whether a punch is
 * late / early and how many minutes were worked. Times are 'H:i:s' strings.
 */
final readonly class WorkSchedule
{
    public function __construct(
        public string $startsAt,
        public string $endsAt,
        public int $graceMinutes,
    ) {}

    public static function default(): self
    {
        return new self('09:00:00', '17:00:00', 15);
    }

    public function isLate(Carbon $checkIn): bool
    {
        $threshold = $checkIn->copy()->setTimeFromTimeString($this->startsAt)->addMinutes($this->graceMinutes);

        return $checkIn->greaterThan($threshold);
    }

    public function isEarlyLeave(Carbon $checkOut): bool
    {
        return $checkOut->lessThan($checkOut->copy()->setTimeFromTimeString($this->endsAt));
    }

    public function workedMinutes(Carbon $checkIn, Carbon $checkOut): int
    {
        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return 0;
        }

        return (int) round($checkIn->diffInMinutes($checkOut));
    }
}
