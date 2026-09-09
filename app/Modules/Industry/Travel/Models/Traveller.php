<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Database\Factories\TravellerFactory;
use App\Modules\Industry\Travel\Domain\TravellerGender;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A person who travels — the passenger on a {@see Booking} and the applicant on
 * a {@see VisaApplication}. Optionally linked to the CRM {@see Customer} that
 * pays for the trip.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $customer_id
 * @property int|null $created_by
 * @property string $full_name
 * @property TravellerGender $gender
 * @property Carbon|null $date_of_birth
 * @property string $nationality
 * @property string|null $passport_number
 * @property Carbon|null $passport_expiry
 * @property string|null $passport_issue_country
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id', 'full_name', 'gender', 'date_of_birth', 'nationality',
    'passport_number', 'passport_expiry', 'passport_issue_country',
    'phone', 'email', 'address', 'notes',
])]
class Traveller extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TravellerFactory> */
    use HasFactory;

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<VisaApplication, $this> */
    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class);
    }

    /** @return HasMany<BookingPassenger, $this> */
    public function bookingPassengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => TravellerGender::class,
            'date_of_birth' => 'date',
            'passport_expiry' => 'date',
        ];
    }

    protected static function newFactory(): TravellerFactory
    {
        return TravellerFactory::new();
    }
}
