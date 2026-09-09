<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Models;

use App\Modules\Industry\Travel\Database\Factories\PackageItineraryItemFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One day of a tour / Umrah / Hajj package {@see Booking}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $booking_id
 * @property int $day_number
 * @property string $title
 * @property string|null $description
 * @property string|null $city
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['booking_id', 'day_number', 'title', 'description', 'city'])]
class PackageItineraryItem extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PackageItineraryItemFactory> */
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
            'day_number' => 'integer',
        ];
    }

    protected static function newFactory(): PackageItineraryItemFactory
    {
        return PackageItineraryItemFactory::new();
    }
}
