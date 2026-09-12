<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\Finance\Database\Factories\InvoiceRefundFactory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Money returned to a customer against an {@see Invoice}, optionally tied to a
 * specific {@see InvoicePayment}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $invoice_id
 * @property int|null $payment_id
 * @property int|null $recorded_by
 * @property string $amount
 * @property Carbon $refunded_on
 * @property PaymentMethod $method
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['invoice_id', 'payment_id', 'amount', 'refunded_on', 'method', 'reason'])]
class InvoiceRefund extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<InvoiceRefundFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<InvoicePayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(InvoicePayment::class, 'payment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'refunded_on' => 'date',
        ];
    }

    protected static function newFactory(): InvoiceRefundFactory
    {
        return InvoiceRefundFactory::new();
    }
}
