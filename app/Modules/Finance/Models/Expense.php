<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\Finance\Database\Factories\ExpenseFactory;
use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\ExpenseStatus;
use App\Modules\Finance\Domain\PaymentMethod;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A recorded expense for a tenant: office, employee, supplier or other spend.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $employee_id
 * @property int|null $recorded_by
 * @property ExpenseCategory $category
 * @property string|null $supplier_name
 * @property string $title
 * @property string $amount
 * @property Carbon $spent_on
 * @property PaymentMethod $method
 * @property string|null $reference
 * @property string|null $note
 * @property ExpenseStatus $status
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $decision_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['employee_id', 'category', 'supplier_name', 'title', 'amount', 'spent_on', 'method', 'reference', 'note'])]
class Expense extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ExpenseCategory::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'spent_on' => 'date',
            'status' => ExpenseStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}
