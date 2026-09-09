<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\CRM\Database\Factories\FollowUpFactory;
use App\Modules\CRM\Domain\FollowUpStatus;
use App\Modules\CRM\Domain\FollowUpType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A scheduled touchpoint on a {@see Lead} or {@see Customer}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $followupable_type
 * @property int $followupable_id
 * @property int|null $assigned_employee_id
 * @property int|null $created_by
 * @property FollowUpType $type
 * @property Carbon $due_at
 * @property FollowUpStatus $status
 * @property string|null $notes
 * @property string|null $outcome
 * @property Carbon|null $completed_at
 * @property Carbon|null $reminder_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['assigned_employee_id', 'type', 'due_at', 'notes'])]
class FollowUp extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<FollowUpFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function followupable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Employee, $this> */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status->isPending() && $this->due_at->isPast();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FollowUpType::class,
            'status' => FollowUpStatus::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): FollowUpFactory
    {
        return FollowUpFactory::new();
    }
}
