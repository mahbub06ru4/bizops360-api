<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Modules\Billing\Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A platform-wide subscription plan — not tenant-owned; every tenant chooses
 * from the same catalogue.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $price_amount
 * @property string $billing_interval
 * @property array<string, mixed>|null $features
 * @property bool $is_active
 */
#[Fillable(['code', 'name', 'price_amount', 'billing_interval', 'features', 'is_active'])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected function casts(): array
    {
        return [
            'price_amount' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }
}
