<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\CRM\Database\Factories\LeadFactory;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * A prospect moving through the sales pipeline for a tenant.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $owner_employee_id
 * @property int|null $converted_customer_id
 * @property int|null $created_by
 * @property string $name
 * @property string|null $company
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $source
 * @property LeadStage $stage
 * @property string|null $estimated_value
 * @property string|null $notes
 * @property string|null $lost_reason
 * @property Carbon|null $converted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['owner_employee_id', 'name', 'company', 'email', 'phone', 'source', 'estimated_value', 'notes'])]
class Lead extends Model
{
    use BelongsToTenant;
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Employee, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_employee_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return MorphMany<Contact, $this> */
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    /** @return MorphMany<CrmActivity, $this> */
    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'subject');
    }

    /** @return MorphMany<FollowUp, $this> */
    public function followups(): MorphMany
    {
        return $this->morphMany(FollowUp::class, 'followupable');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => LeadStage::class,
            'estimated_value' => 'decimal:2',
            'converted_at' => 'datetime',
        ];
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
