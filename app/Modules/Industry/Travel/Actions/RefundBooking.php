<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\RefundBookingData;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Refunds money to the customer for a booking. The running `refund_amount` may
 * never exceed what the customer was billed (`sell_amount`).
 */
class RefundBooking
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Booking $booking, RefundBookingData $data): Booking
    {
        $this->assertTenantOwns($booking);

        $refund = Money::fromDecimal($data->amount);

        if (! $refund->isPositive()) {
            throw ValidationException::withMessages(['amount' => 'A refund must be greater than zero.']);
        }

        return DB::transaction(function () use ($booking, $data, $refund): Booking {
            /** @var Booking $locked */
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());

            $newRefund = Money::fromDecimal($locked->refund_amount)->add($refund);

            if ($newRefund->greaterThan(Money::fromDecimal($locked->sell_amount))) {
                throw ValidationException::withMessages([
                    'amount' => 'The total refund cannot exceed the amount billed to the customer.',
                ]);
            }

            $locked->refund_amount = $newRefund->toDecimalString();
            $locked->refund_on = Carbon::parse($data->refundedOn);
            $locked->status = BookingStatus::Refunded;

            if ($data->reason !== null) {
                $locked->notes = trim(($locked->notes ?? '')."\nRefund: {$data->reason}");
            }

            $locked->save();

            return $locked->refresh()->load(['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
