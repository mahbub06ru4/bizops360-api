<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Modules\Industry\Travel\Database\Factories\BookingSegmentFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One flight leg on an air-ticket {@see Booking}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $booking_id
 * @property int $sequence
 * @property string $flight_number
 * @property string|null $airline
 * @property string $from_airport
 * @property string $to_airport
 * @property Carbon $depart_at
 * @property Carbon|null $arrive_at
 * @property string|null $cabin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'booking_id', 'sequence', 'flight_number', 'airline',
    'from_airport', 'to_airport', 'depart_at', 'arrive_at', 'cabin',
])]
class BookingSegment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BookingSegmentFactory> */
    use HasFactory;

    /** @return BelongsTo<Booking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'depart_at' => 'datetime',
            'arrive_at' => 'datetime',
        ];
    }

    protected static function newFactory(): BookingSegmentFactory
    {
        return BookingSegmentFactory::new();
    }
}
