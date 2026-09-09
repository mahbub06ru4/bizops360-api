<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Actions\Concerns\WritesBookingChildren;
use App\Modules\Industry\Travel\Data\BookingData;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBooking
{
    use InteractsWithTenant;
    use WritesBookingChildren;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Booking $booking, BookingData $data): Booking
    {
        $this->assertTenantOwns($booking);

        if (! $booking->status->isEditable()) {
            throw ValidationException::withMessages([
                'booking' => "A {$booking->status->value} booking can no longer be edited.",
            ]);
        }

        $this->assertReferenceOwned($data->customerId, Customer::class);
        $this->assertReferenceOwned($data->handledByEmployeeId, Employee::class);

        return DB::transaction(function () use ($booking, $data): Booking {
            $booking->fill($data->toAttributes())->save();
            $this->syncBookingChildren($booking, $data);

            return $booking->refresh()->load(['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
