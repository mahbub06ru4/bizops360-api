<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Database\Factories\InvoiceFactory;
use App\Modules\Finance\Domain\InvoiceBalance;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Domain\Money;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A sales invoice raised against a CRM customer.
 *
 * `amount_paid` / `amount_refunded` are running totals maintained by the
 * payment and refund actions inside a transaction; `amount_due` and `status`
 * are derived from them via {@see InvoiceBalance}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $customer_id
 * @property int|null $created_by
 * @property string $number
 * @property string $customer_name
 * @property InvoiceStatus $status
 * @property Carbon $issue_date
 * @property Carbon|null $due_date
 * @property string $amount
 * @property string $amount_paid
 * @property string $amount_refunded
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_id', 'customer_name', 'number', 'status', 'issue_date', 'due_date', 'amount', 'notes'])]
class Invoice extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<InvoicePayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /** @return HasMany<InvoiceRefund, $this> */
    public function refunds(): HasMany
    {
        return $this->hasMany(InvoiceRefund::class);
    }

    public function balance(): InvoiceBalance
    {
        return new InvoiceBalance(
            Money::fromDecimal($this->amount),
            Money::fromDecimal($this->amount_paid),
            Money::fromDecimal($this->amount_refunded),
        );
    }

    public function amountDue(): string
    {
        return $this->balance()->due()->toDecimalString();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_refunded' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    protected static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
