<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\BookingType;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Industry\Travel\Models\VisaApplication;
use Illuminate\Support\Carbon;

/**
 * The travel desk dashboard for the current tenant: visa pipeline, booking mix,
 * upcoming departures and the money the desk has earned. Tenant-scoped by the
 * models' global scope.
 */
class BuildTravelDeskOverview
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        $visaByStage = [];
        $rawVisa = VisaApplication::query()->selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage')->all();
        foreach (VisaStage::values() as $stage) {
            $visaByStage[$stage] = (int) ($rawVisa[$stage] ?? 0);
        }

        $bookingByStatus = [];
        $rawStatus = Booking::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status')->all();
        foreach (BookingStatus::values() as $status) {
            $bookingByStatus[$status] = (int) ($rawStatus[$status] ?? 0);
        }

        $bookingByType = [];
        $rawType = Booking::query()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type')->all();
        foreach (BookingType::values() as $type) {
            $bookingByType[$type] = (int) ($rawType[$type] ?? 0);
        }

        return [
            'travellers' => Traveller::query()->count(),
            'visas' => [
                'by_stage' => $visaByStage,
                'in_progress' => VisaApplication::query()->whereNotIn('stage', [
                    VisaStage::Approved->value, VisaStage::Rejected->value, VisaStage::Cancelled->value,
                ])->count(),
                'submitted_this_month' => VisaApplication::query()->where('submitted_on', '>=', $monthStart)->count(),
                'approved_this_month' => VisaApplication::query()
                    ->where('stage', VisaStage::Approved->value)
                    ->where('decision_on', '>=', $monthStart)->count(),
            ],
            'bookings' => [
                'by_status' => $bookingByStatus,
                'by_type' => $bookingByType,
                'departing_next_7_days' => Booking::query()
                    ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Ticketed->value])
                    ->whereBetween('depart_on', [$now->copy()->startOfDay(), $now->copy()->addDays(7)->endOfDay()])
                    ->count(),
                'departing_next_30_days' => Booking::query()
                    ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Ticketed->value])
                    ->whereBetween('depart_on', [$now->copy()->startOfDay(), $now->copy()->addDays(30)->endOfDay()])
                    ->count(),
                'awaiting_invoice' => Booking::query()
                    ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Ticketed->value, BookingStatus::Completed->value])
                    ->whereNull('invoice_id')
                    ->count(),
            ],
            'earnings' => [
                'all_time' => $this->earnings(null),
                'this_month' => $this->earnings($monthStart),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function earnings(?Carbon $since): array
    {
        $query = Booking::query()->whereIn('status', [
            BookingStatus::Confirmed->value, BookingStatus::Ticketed->value, BookingStatus::Completed->value, BookingStatus::Refunded->value,
        ]);

        if ($since !== null) {
            $query->where('issued_on', '>=', $since);
        }

        $sell = Money::zero();
        $cost = Money::zero();
        $commission = Money::zero();
        $refund = Money::zero();

        foreach ($query->get(['sell_amount', 'cost_amount', 'commission_amount', 'refund_amount']) as $booking) {
            $sell = $sell->add(Money::fromDecimal($booking->sell_amount));
            $cost = $cost->add(Money::fromDecimal($booking->cost_amount));
            $commission = $commission->add(Money::fromDecimal($booking->commission_amount));
            $refund = $refund->add(Money::fromDecimal($booking->refund_amount));
        }

        $net = $sell->subtract($cost)->add($commission)->subtract($refund);

        return [
            'sell' => $sell->toDecimalString(),
            'cost' => $cost->toDecimalString(),
            'commission' => $commission->toDecimalString(),
            'refund' => $refund->toDecimalString(),
            'net_profit' => $net->toDecimalString(),
        ];
    }
}
