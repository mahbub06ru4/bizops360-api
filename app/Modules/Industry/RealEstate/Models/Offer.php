<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Database\Factories\OfferFactory;
use App\Modules\Industry\RealEstate\Domain\OfferedBy;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One entry in a structured negotiation chain for a {@see Lead} against a
 * {@see Unit}. A counter-offer never mutates the row it responds to — it
 * creates a new row pointing back at it via `previous_offer_id`, so the full
 * history is always reconstructable (roadmap §7: "structured Offer/Negotiation
 * with history, not chat").
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $lead_id
 * @property int $unit_id
 * @property int|null $previous_offer_id
 * @property string $offered_price
 * @property OfferedBy $offered_by
 * @property OfferStatus $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'unit_id', 'previous_offer_id', 'offered_price', 'offered_by', 'notes'])]
class Offer extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<OfferFactory> */
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
    public function previousOffer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_offer_id');
    }

    /** @return HasMany<Offer, $this> */
    public function counterOffers(): HasMany
    {
        return $this->hasMany(self::class, 'previous_offer_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'offered_price' => 'decimal:2',
            'offered_by' => OfferedBy::class,
            'status' => OfferStatus::class,
        ];
    }

    protected static function newFactory(): OfferFactory
    {
        return OfferFactory::new();
    }
}
