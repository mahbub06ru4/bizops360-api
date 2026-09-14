<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelBooking
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateBooking $booking): RealEstateBooking
    {
        $this->assertTenantOwns($booking);

        if (! in_array($booking->status, [BookingStatus::Reserved, BookingStatus::Booked], true)) {
            throw ValidationException::withMessages([
                'booking' => "A {$booking->status->value} booking cannot be cancelled.",
            ]);
        }

        return DB::transaction(function () use ($booking): RealEstateBooking {
            $unit = $booking->unit()->firstOrFail();
            $unit->status = UnitStatus::Available;
            $unit->save();

            $booking->status = BookingStatus::Cancelled;
            $booking->save();

            return $booking->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
