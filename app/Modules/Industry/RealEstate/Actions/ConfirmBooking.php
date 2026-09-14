<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\ConvertLead;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Finalises a reservation into a sale: marks the unit `sold`, and converts
 * the lead into a CRM customer through CRM's own {@see ConvertLead} action —
 * never by writing to the {@see Lead} model directly.
 */
class ConfirmBooking
{
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly ConvertLead $convertLead,
    ) {}

    public function handle(RealEstateBooking $booking, User $actor): RealEstateBooking
    {
        $this->assertTenantOwns($booking);

        if ($booking->status !== BookingStatus::Reserved) {
            throw ValidationException::withMessages([
                'booking' => "A {$booking->status->value} booking cannot be confirmed.",
            ]);
        }

        return DB::transaction(function () use ($booking, $actor): RealEstateBooking {
            $unit = $booking->unit()->firstOrFail();
            $unit->status = UnitStatus::Sold;
            $unit->save();

            $lead = $booking->lead()->firstOrFail();

            if ($lead->converted_at === null) {
                $customer = $this->convertLead->handle($lead, [], $actor);
                $booking->customer_id = (int) $customer->getKey();
            } else {
                $booking->customer_id = $lead->converted_customer_id;
            }

            $booking->status = BookingStatus::Booked;
            $booking->booked_at = Carbon::now();
            $booking->save();

            return $booking->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
