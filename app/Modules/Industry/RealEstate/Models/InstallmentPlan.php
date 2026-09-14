<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\InstallmentPlanFactory;
use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The payment schedule for one {@see RealEstateBooking} — a down payment plus
 * an even split of the remainder across `installment_count` {@see Installment}
 * rows, generated together in {@see \App\Modules\Industry\RealEstate\Actions\CreateInstallmentPlan}.
 * One plan per booking (enforced by a unique index on `booking_id`).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $booking_id
 * @property string $down_payment_amount
 * @property int $installment_count
 * @property PaymentPlanFrequency $frequency
 * @property Carbon $start_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['booking_id', 'down_payment_amount', 'installment_count', 'frequency', 'start_date'])]
class InstallmentPlan extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<InstallmentPlanFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateBooking, $this> */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(RealEstateBooking::class, 'booking_id');
    }

    /** @return HasMany<Installment, $this> */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('sequence');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'down_payment_amount' => 'decimal:2',
            'installment_count' => 'integer',
            'frequency' => PaymentPlanFrequency::class,
            'start_date' => 'date',
        ];
    }

    protected static function newFactory(): InstallmentPlanFactory
    {
        return InstallmentPlanFactory::new();
    }
}
