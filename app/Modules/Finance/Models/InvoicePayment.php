<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Database\Factories\InvoicePaymentFactory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A payment received against an {@see Invoice}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $invoice_id
 * @property int|null $customer_id
 * @property int|null $recorded_by
 * @property string $amount
 * @property Carbon $paid_on
 * @property PaymentMethod $method
 * @property string|null $reference
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['invoice_id', 'customer_id', 'amount', 'paid_on', 'method', 'reference', 'note'])]
class InvoicePayment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<InvoicePaymentFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
            'paid_on' => 'date',
        ];
    }

    protected static function newFactory(): InvoicePaymentFactory
    {
        return InvoicePaymentFactory::new();
    }
}
