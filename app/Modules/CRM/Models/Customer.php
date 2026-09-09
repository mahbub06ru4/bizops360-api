<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\CRM\Database\Factories\CustomerFactory;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A customer of a tenant — usually a converted {@see Lead}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $owner_employee_id
 * @property int|null $created_by
 * @property string $name
 * @property string $type
 * @property string|null $company
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['owner_employee_id', 'name', 'type', 'company', 'email', 'phone', 'address'])]
class Customer extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /** @return BelongsTo<Employee, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_employee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasOne<Lead, $this> */
    public function sourceLead(): HasOne
    {
        return $this->hasOne(Lead::class, 'converted_customer_id');
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
