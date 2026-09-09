<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Modules\HR\Database\Factories\LeaveTypeFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A category of leave a tenant offers (annual, sick, unpaid, …).
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $code
 * @property int $default_days_per_year
 * @property bool $is_paid
 * @property bool $requires_approval
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'code', 'default_days_per_year', 'is_paid', 'requires_approval'])]
class LeaveType extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<LeaveTypeFactory> */
    use HasFactory;

    /** @return HasMany<LeaveRequest, $this> */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_days_per_year' => 'integer',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    protected static function newFactory(): LeaveTypeFactory
    {
        return LeaveTypeFactory::new();
    }
}
