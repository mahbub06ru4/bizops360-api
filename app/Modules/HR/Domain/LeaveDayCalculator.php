<?php

declare(strict_types=1);

namespace App\Modules\HR\Domain;

use Illuminate\Support\Carbon;

/**
 * Counts the chargeable days in a leave range: every calendar day from start to
 * end inclusive, minus any tenant holiday that falls within the range.
 *
 * Weekend handling is intentionally left out for the MVP — a tenant that does not
 * work weekends records them as recurring holidays.
 */
class LeaveDayCalculator
{
    /**
     * @param  array<int, string>  $holidayDates  ISO date strings (Y-m-d) of tenant holidays
     */
    public function countChargeableDays(Carbon $start, Carbon $end, array $holidayDates): int
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $total = (int) round($start->diffInDays($end)) + 1;

        $holidaysInRange = 0;
        foreach (array_unique($holidayDates) as $date) {
            $day = Carbon::parse($date)->startOfDay();
            if ($day->gte($start) && $day->lte($end)) {
                $holidaysInRange++;
            }
        }

        return max(0, $total - $holidaysInRange);
    }
}
