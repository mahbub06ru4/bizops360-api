<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteBooking
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Booking $booking): void
    {
        $this->assertTenantOwns($booking);

        if ($booking->status !== BookingStatus::Quoted || $booking->invoice_id !== null) {
            throw ValidationException::withMessages([
                'booking' => 'Only a quoted booking with no invoice can be deleted; cancel it instead.',
            ]);
        }

        $booking->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
