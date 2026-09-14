<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Finance\Actions\CreateInvoice;
use App\Modules\Finance\Data\InvoiceData;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\InstallmentStatus;
use App\Modules\Industry\RealEstate\Models\Installment;
use App\Modules\Industry\Travel\Actions\RaiseInvoiceForBooking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Raises a Finance invoice for a due installment and links the two — mirrors
 * {@see RaiseInvoiceForBooking}: the
 * invoice is created entirely through Finance's own {@see CreateInvoice}
 * action, RealEstate never touches Finance's models directly.
 */
class GenerateInstallmentInvoice
{
    use InteractsWithTenant;

    public function __construct(
        private readonly TenantContext $context,
        private readonly CreateInvoice $createInvoice,
    ) {}

    public function handle(Installment $installment, User $actor): Installment
    {
        $this->assertTenantOwns($installment);

        if ($installment->invoice_id !== null) {
            throw ValidationException::withMessages(['installment' => 'This installment already has an invoice.']);
        }

        if ($installment->status !== InstallmentStatus::Pending) {
            throw ValidationException::withMessages([
                'installment' => "A {$installment->status->value} installment cannot be invoiced.",
            ]);
        }

        $plan = $installment->installmentPlan()->with('booking.unit.building.project')->firstOrFail();
        $booking = $plan->booking;

        if ($booking->customer_id === null) {
            throw ValidationException::withMessages(['installment' => 'The booking has no customer to invoice yet — confirm it first.']);
        }

        $unit = $booking->unit;
        $project = $unit->building?->project;
        $label = $project !== null
            ? "{$project->name} — unit {$unit->unit_number}, installment #{$installment->sequence}"
            : "Installment #{$installment->sequence}";

        return DB::transaction(function () use ($installment, $booking, $label, $actor): Installment {
            $invoice = $this->createInvoice->handle(
                InvoiceData::fromArray([
                    'customer_id' => $booking->customer_id,
                    'issue_date' => Carbon::now()->toDateString(),
                    'due_date' => $installment->due_date->toDateString(),
                    'amount' => $installment->amount,
                    'notes' => $label,
                ]),
                $actor,
            );

            $installment->invoice_id = (int) $invoice->getKey();
            $installment->status = InstallmentStatus::Invoiced;
            $installment->save();

            return $installment->refresh()->load('invoice');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
