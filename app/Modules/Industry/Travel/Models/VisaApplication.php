<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Database\Factories\VisaApplicationFactory;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A visa case for one traveller and one destination, moving through the
 * {@see VisaStage} pipeline.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $traveller_id
 * @property int|null $customer_id
 * @property int|null $assigned_employee_id
 * @property int|null $created_by
 * @property string $destination_country
 * @property string $visa_type
 * @property string|null $mission
 * @property VisaStage $stage
 * @property string|null $reference_no
 * @property string|null $application_no
 * @property string $government_fee
 * @property string $service_charge
 * @property Carbon|null $submitted_on
 * @property Carbon|null $decision_on
 * @property string|null $decision_note
 * @property Carbon|null $expected_travel_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id', 'assigned_employee_id', 'destination_country', 'visa_type',
    'mission', 'reference_no', 'application_no', 'government_fee', 'service_charge',
    'expected_travel_date',
])]
class VisaApplication extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<VisaApplicationFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Traveller, $this> */
    public function traveller(): BelongsTo
    {
        return $this->belongsTo(Traveller::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<VisaRequirement, $this> */
    public function requirements(): HasMany
    {
        return $this->hasMany(VisaRequirement::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => VisaStage::class,
            'government_fee' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'submitted_on' => 'date',
            'decision_on' => 'date',
            'expected_travel_date' => 'date',
        ];
    }

    protected static function newFactory(): VisaApplicationFactory
    {
        return VisaApplicationFactory::new();
    }
}
