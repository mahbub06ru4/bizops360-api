<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\HR\Database\Factories\AttendanceFactory;
use App\Modules\HR\Domain\AttendanceStatus;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One employee's attendance record for one day.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $employee_id
 * @property Carbon $date
 * @property Carbon|null $check_in_at
 * @property Carbon|null $check_out_at
 * @property AttendanceStatus $status
 * @property int|null $worked_minutes
 * @property bool $is_late
 * @property bool $is_early_leave
 * @property string|null $note
 * @property int|null $recorded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['employee_id', 'date', 'check_in_at', 'check_out_at', 'status', 'worked_minutes', 'is_late', 'is_early_leave', 'note'])]
class Attendance extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'status' => AttendanceStatus::class,
            'worked_minutes' => 'integer',
            'is_late' => 'boolean',
            'is_early_leave' => 'boolean',
        ];
    }

    protected static function newFactory(): AttendanceFactory
    {
        return AttendanceFactory::new();
    }
}
