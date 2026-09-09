<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * An immutable audit entry for something that happened to a lead or customer.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $causer_id
 * @property string $event
 * @property string $description
 * @property array<string, mixed>|null $properties
 * @property Carbon|null $created_at
 */
#[Fillable(['event', 'description', 'properties'])]
class CrmActivity extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }
}
