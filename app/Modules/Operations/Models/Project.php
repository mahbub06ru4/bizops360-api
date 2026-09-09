<?php

declare(strict_types=1);

namespace App\Modules\Operations\Models;

use App\Models\User;
use App\Modules\Operations\Database\Factories\ProjectFactory;
use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A body of work for a tenant, optionally owned by a department / lead employee,
 * grouping tasks.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $department_id
 * @property int|null $lead_employee_id
 * @property int|null $created_by
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property ProjectStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $due_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['department_id', 'lead_employee_id', 'name', 'code', 'description', 'status', 'start_date', 'due_date'])]
class Project extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'lead_employee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }
}
