<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Modules\Audit\Concerns\LogsBusinessActivity;
use App\Modules\Billing\Database\Factories\SubscriptionFactory;
use App\Modules\Billing\Domain\SubscriptionStatus;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One tenant's subscription. Exactly one per tenant (unique `tenant_id`).
 * Lifecycle is owned by the Actions in `App\Modules\Billing\Actions`; no
 * payment-gateway integration yet — see docs/ops/DEPLOY.md.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_ends_at
 * @property Carbon|null $canceled_at
 */
#[Fillable(['plan_id', 'status', 'trial_ends_at', 'current_period_ends_at', 'canceled_at'])]
class Subscription extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    use LogsBusinessActivity;

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }
}
