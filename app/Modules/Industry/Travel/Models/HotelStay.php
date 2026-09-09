<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Modules\Industry\Travel\Database\Factories\HotelStayFactory;
use App\Modules\Industry\Travel\Domain\BoardBasis;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A hotel reservation within a {@see Booking} (a standalone hotel booking, or
 * one hotel leg of a package).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $booking_id
 * @property string $hotel_name
 * @property string $city
 * @property string|null $country
 * @property Carbon $check_in
 * @property Carbon $check_out
 * @property int $nights
 * @property string|null $room_type
 * @property int $rooms
 * @property int $guests
 * @property BoardBasis $board_basis
 * @property string|null $confirmation_no
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'hotel_name', 'city', 'country', 'check_in', 'check_out', 'nights',
    'room_type', 'rooms', 'guests', 'board_basis', 'confirmation_no', 'note',
])]
class HotelStay extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<HotelStayFactory> */
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
            'board_basis' => BoardBasis::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'nights' => 'integer',
            'rooms' => 'integer',
            'guests' => 'integer',
        ];
    }

    protected static function newFactory(): HotelStayFactory
    {
        return HotelStayFactory::new();
    }
}
