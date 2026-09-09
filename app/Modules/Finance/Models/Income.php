<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Database\Factories\IncomeFactory;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A recorded income entry for a tenant — a customer payment taken outside the
 * invoice flow, or any other money coming in.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $customer_id
 * @property int|null $recorded_by
 * @property IncomeCategory $category
 * @property string|null $source
 * @property string $amount
 * @property Carbon $received_on
 * @property PaymentMethod $method
 * @property string|null $reference
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['customer_id', 'category', 'source', 'amount', 'received_on', 'method', 'reference', 'note'])]
class Income extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<IncomeFactory> */
    use HasFactory;

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
            'category' => IncomeCategory::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'received_on' => 'date',
        ];
    }

    protected static function newFactory(): IncomeFactory
    {
        return IncomeFactory::new();
    }
}
