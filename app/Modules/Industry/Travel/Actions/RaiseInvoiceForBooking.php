<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\CreateInvoice;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Raises a Finance invoice for a booking's sell price and links the two. The
 * invoice itself is created through the Finance module's own action — Travel
 * never touches Finance's models directly.
 */
class RaiseInvoiceForBooking
{
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly CreateInvoice $createInvoice,
    ) {}

    public function handle(Booking $booking, User $actor, ?string $issueDate = null, ?string $dueDate = null): Booking
    {
        $this->assertTenantOwns($booking);

        if ($booking->invoice_id !== null) {
            throw ValidationException::withMessages(['booking' => 'This booking already has an invoice.']);
        }

        if ($booking->customer_id === null) {
            throw ValidationException::withMessages(['booking' => 'Attach a customer to the booking before invoicing it.']);
        }

        // Only issued business is invoiceable: air tickets move to Ticketed,
        // everything else (hotel, package, transport, …) moves to Confirmed —
        // see IssueBooking. A still-Quoted, Cancelled or Refunded booking is not.
        if (! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::Ticketed, BookingStatus::Completed], true)) {
            throw ValidationException::withMessages(['booking' => "A {$booking->status->value} booking cannot be invoiced — issue it first."]);
        }

        return DB::transaction(function () use ($booking, $actor, $issueDate, $dueDate): Booking {
            $invoice = $this->createInvoice->handle(
                InvoiceData::fromArray([
                    'customer_id' => $booking->customer_id,
                    'issue_date' => $issueDate ?? Carbon::now()->toDateString(),
                    'due_date' => $dueDate,
                    'amount' => $booking->sell_amount,
                    'notes' => "Booking {$booking->reference} — {$booking->title}",
                ]),
                $actor,
            );

            $booking->invoice_id = (int) $invoice->getKey();
            $booking->save();

            return $booking->refresh()->load(['customer', 'invoice', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
