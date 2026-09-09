<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Modules\HR\Database\Factories\LeaveBalanceFactory;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An employee's leave entitlement and usage for one leave type in one year.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $year
 * @property int $entitled_days
 * @property int $used_days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['employee_id', 'leave_type_id', 'year', 'entitled_days', 'used_days'])]
class LeaveBalance extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<LeaveBalanceFactory> */
    use HasFactory;

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'entitled_days' => 'integer',
            'used_days' => 'integer',
        ];
    }

    protected static function newFactory(): LeaveBalanceFactory
    {
        return LeaveBalanceFactory::new();
    }
}
