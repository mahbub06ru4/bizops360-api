<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Models\User;
use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Domain\Money;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Industry\Travel\Database\Factories\BookingFactory;
use App\Modules\Industry\Travel\Domain\BookingProfit;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\BookingType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A single sale by the travel desk — an air ticket, hotel stay, tour or Umrah
 * package, transport hire or insurance policy. Rich detail hangs off it:
 * {@see BookingPassenger}, {@see BookingSegment} (flights), {@see HotelStay},
 * {@see PackageItineraryItem}.
 *
 * `cost_amount` / `sell_amount` / `commission_amount` drive {@see BookingProfit};
 * `invoice_id` links the Finance invoice once one is raised.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $customer_id
 * @property int|null $handled_by_employee_id
 * @property int|null $invoice_id
 * @property int|null $created_by
 * @property string $reference
 * @property BookingType $type
 * @property string $title
 * @property string|null $supplier_name
 * @property string|null $pnr
 * @property string|null $airline
 * @property string|null $origin
 * @property string|null $destination
 * @property Carbon|null $depart_on
 * @property Carbon|null $return_on
 * @property BookingStatus $status
 * @property string $cost_amount
 * @property string $sell_amount
 * @property string $commission_amount
 * @property string $refund_amount
 * @property string $currency
 * @property Carbon|null $issued_on
 * @property Carbon|null $cancelled_on
 * @property Carbon|null $refund_on
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id', 'handled_by_employee_id', 'type', 'title', 'supplier_name',
    'pnr', 'airline', 'origin', 'destination', 'depart_on', 'return_on',
    'cost_amount', 'sell_amount', 'commission_amount', 'currency', 'notes',
])]
class Booking extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'handled_by_employee_id');
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<BookingPassenger, $this> */
    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    /** @return HasMany<BookingSegment, $this> */
    public function segments(): HasMany
    {
        return $this->hasMany(BookingSegment::class)->orderBy('sequence');
    }

    /** @return HasMany<HotelStay, $this> */
    public function hotelStays(): HasMany
    {
        return $this->hasMany(HotelStay::class);
    }

    /** @return HasMany<PackageItineraryItem, $this> */
    public function itinerary(): HasMany
    {
        return $this->hasMany(PackageItineraryItem::class)->orderBy('day_number');
    }

    public function profit(): BookingProfit
    {
        return new BookingProfit(
            Money::fromDecimal($this->sell_amount),
            Money::fromDecimal($this->cost_amount),
            Money::fromDecimal($this->commission_amount),
            Money::fromDecimal($this->refund_amount),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BookingType::class,
            'status' => BookingStatus::class,
            'cost_amount' => 'decimal:2',
            'sell_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'depart_on' => 'date',
            'return_on' => 'date',
            'issued_on' => 'date',
            'cancelled_on' => 'date',
            'refund_on' => 'date',
        ];
    }

    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }
}
