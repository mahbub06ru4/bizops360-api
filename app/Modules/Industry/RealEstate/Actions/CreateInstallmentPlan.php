<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\InstallmentPlanData;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Industry\RealEstate\Domain\InstallmentStatus;
use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use App\Modules\Industry\RealEstate\Models\Installment;
use App\Modules\Industry\RealEstate\Models\InstallmentPlan;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Generates an installment plan and its child installments for a confirmed
 * booking in one transaction, splitting the remaining balance evenly across
 * `installment_count` due dates and rounding the last installment to absorb
 * the remainder cents so the sum always reconciles to the balance exactly.
 */
class CreateInstallmentPlan
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateBooking $booking, InstallmentPlanData $data): InstallmentPlan
    {
        $this->assertTenantOwns($booking);

        if (! in_array($booking->status, [BookingStatus::Booked, BookingStatus::Completed], true)) {
            throw ValidationException::withMessages([
                'booking' => 'An installment plan can only be created for a booked (confirmed) booking.',
            ]);
        }

        if ($booking->installmentPlan()->exists()) {
            throw ValidationException::withMessages([
                'booking' => 'This booking already has an installment plan.',
            ]);
        }

        if ($data->installmentCount < 1) {
            throw ValidationException::withMessages([
                'installment_count' => 'There must be at least one installment.',
            ]);
        }

        $balanceCents = (int) round(((float) $booking->agreed_price - (float) $data->downPaymentAmount) * 100);

        if ($balanceCents < 0) {
            throw ValidationException::withMessages([
                'down_payment_amount' => 'The down payment cannot exceed the agreed price.',
            ]);
        }

        return DB::transaction(function () use ($booking, $data, $balanceCents): InstallmentPlan {
            $plan = new InstallmentPlan([
                'down_payment_amount' => $data->downPaymentAmount,
                'installment_count' => $data->installmentCount,
                'frequency' => $data->frequency,
                'start_date' => $data->startDate,
            ]);
            $plan->tenant_id = (int) $booking->tenant_id;
            $plan->booking_id = $booking->getKey();
            $plan->save();

            $perInstallmentCents = intdiv($balanceCents, $data->installmentCount);
            $remainderCents = $balanceCents - ($perInstallmentCents * $data->installmentCount);
            $startDate = Carbon::parse($data->startDate);

            for ($sequence = 1; $sequence <= $data->installmentCount; $sequence++) {
                $amountCents = $perInstallmentCents;

                if ($sequence === $data->installmentCount) {
                    $amountCents += $remainderCents;
                }

                $installment = new Installment([
                    'sequence' => $sequence,
                    'due_date' => $this->dueDate($startDate, $data->frequency, $sequence)->toDateString(),
                    'amount' => number_format($amountCents / 100, 2, '.', ''),
                    'status' => InstallmentStatus::Pending,
                ]);
                $installment->tenant_id = (int) $booking->tenant_id;
                $installment->installment_plan_id = $plan->getKey();
                $installment->save();
            }

            return $plan->refresh()->load('installments');
        });
    }

    private function dueDate(Carbon $startDate, PaymentPlanFrequency $frequency, int $sequence): Carbon
    {
        $monthsPerStep = $frequency === PaymentPlanFrequency::Quarterly ? 3 : 1;

        return $startDate->copy()->addMonths($monthsPerStep * ($sequence - 1));
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
