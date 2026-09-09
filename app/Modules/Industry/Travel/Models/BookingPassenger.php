<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Modules\Industry\Travel\Database\Factories\BookingPassengerFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A traveller on a {@see Booking}, with their per-passenger ticket details.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $booking_id
 * @property int $traveller_id
 * @property string|null $ticket_number
 * @property string|null $baggage
 * @property string|null $fare_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['booking_id', 'traveller_id', 'ticket_number', 'baggage', 'fare_amount'])]
class BookingPassenger extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BookingPassengerFactory> */
    use HasFactory;

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<Traveller, $this> */
    public function traveller(): BelongsTo
    {
        return $this->belongsTo(Traveller::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fare_amount' => 'decimal:2',
        ];
    }

    protected static function newFactory(): BookingPassengerFactory
    {
        return BookingPassengerFactory::new();
    }
}
