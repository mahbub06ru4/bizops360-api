<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\InstallmentStatus;
use App\Modules\Industry\RealEstate\Models\Installment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Manually marks an installment paid once Finance confirms the linked
 * invoice is settled. Phase 1 has no payment-gateway webhook, so this is an
 * admin/staff-triggered action, not automatic reconciliation.
 */
class MarkInstallmentPaid
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Installment $installment): Installment
    {
        $this->assertTenantOwns($installment);

        if ($installment->status !== InstallmentStatus::Invoiced && $installment->status !== InstallmentStatus::Overdue) {
            throw ValidationException::withMessages([
                'installment' => "A {$installment->status->value} installment cannot be marked paid — raise its invoice first.",
            ]);
        }

        $installment->status = InstallmentStatus::Paid;
        $installment->save();

        return $installment->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
