<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Models\User;
use App\Modules\Organization\Database\Factories\EmployeeFactory;
use App\Modules\Organization\Domain\EmploymentStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A person employed by a tenant, optionally linked to a login {@see User} and
 * placed within the org structure (branch / department / designation).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $user_id
 * @property int|null $branch_id
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property string $employee_code
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon $hire_date
 * @property EmploymentStatus $employment_status
 * @property-read string $full_name
 */
#[Fillable([
    'user_id', 'branch_id', 'department_id', 'designation_id',
    'employee_code', 'first_name', 'last_name', 'email', 'phone',
    'hire_date', 'employment_status',
])]
class Employee extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Designation, $this> */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'employment_status' => EmploymentStatus::class,
        ];
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }
}
