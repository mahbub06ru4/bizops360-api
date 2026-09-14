<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Database\Factories\RealEstateBookingFactory;
use App\Modules\Industry\RealEstate\Domain\BookingStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * A unit purchase moving through reserve → book → complete. Deliberately its
 * own table/model — not a reuse of {@see \App\Modules\Industry\Travel\Models\Booking}
 * — because buying a unit is a semantically different, vertical-specific
 * concept from a travel booking, even though both use the word "booking".
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $lead_id
 * @property int $unit_id
 * @property int $accepted_offer_id
 * @property int|null $customer_id
 * @property string $agreed_price
 * @property BookingStatus $status
 * @property Carbon|null $booked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'unit_id', 'accepted_offer_id', 'customer_id', 'agreed_price', 'booked_at'])]
class RealEstateBooking extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<RealEstateBookingFactory> */
    use HasFactory;

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return BelongsTo<Offer, $this> */
    public function acceptedOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'accepted_offer_id');
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasOne<InstallmentPlan, $this> */
    public function installmentPlan(): HasOne
    {
        return $this->hasOne(InstallmentPlan::class, 'booking_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'agreed_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'booked_at' => 'datetime',
        ];
    }

    protected static function newFactory(): RealEstateBookingFactory
    {
        return RealEstateBookingFactory::new();
    }
}
