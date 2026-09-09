<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Actions\Concerns\WritesBookingChildren;
use App\Modules\Industry\Travel\Data\BookingData;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\GenerateBookingReference;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateBooking
{
    use InteractsWithTenant;
    use WritesBookingChildren;

    public function __construct(
        private readonly TenantContext $context,
        private readonly GenerateBookingReference $references,
    ) {}

    public function handle(BookingData $data, User $creator): Booking
    {
        $this->assertReferenceOwned($data->customerId, Customer::class);
        $this->assertReferenceOwned($data->handledByEmployeeId, Employee::class);

        $tenantId = $this->currentTenantId();

        return DB::transaction(function () use ($data, $creator, $tenantId): Booking {
            $booking = new Booking($data->toAttributes());
            $booking->tenant_id = $tenantId;
            $booking->created_by = $creator->getKey();
            $booking->reference = $this->references->handle($tenantId);
            $booking->status = BookingStatus::Quoted;
            $booking->refund_amount = '0.00';
            $booking->save();

            $this->syncBookingChildren($booking, $data);

            return $booking->load(['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
