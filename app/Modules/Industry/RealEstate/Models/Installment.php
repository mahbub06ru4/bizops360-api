<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Finance\Models\Invoice;
use App\Modules\Industry\RealEstate\Actions\GenerateInstallmentInvoice;
use App\Modules\Industry\RealEstate\Actions\MarkInstallmentPaid;
use App\Modules\Industry\RealEstate\Database\Factories\InstallmentFactory;
use App\Modules\Industry\RealEstate\Domain\InstallmentStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One due payment on an {@see InstallmentPlan}. `invoice_id` is only set once
 * {@see GenerateInstallmentInvoice}
 * raises a Finance invoice for it — Phase 1 has no payment-gateway webhook, so
 * `status` moves to `paid` only via the manual/admin-triggered
 * {@see MarkInstallmentPaid}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $installment_plan_id
 * @property int $sequence
 * @property Carbon $due_date
 * @property string $amount
 * @property InstallmentStatus $status
 * @property int|null $invoice_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['installment_plan_id', 'sequence', 'due_date', 'amount', 'status', 'invoice_id'])]
class Installment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<InstallmentFactory> */
    use HasFactory;

    /** @return BelongsTo<InstallmentPlan, $this> */
    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'status' => InstallmentStatus::class,
        ];
    }

    protected static function newFactory(): InstallmentFactory
    {
        return InstallmentFactory::new();
    }
}
