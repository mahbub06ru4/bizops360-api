<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\LandShareFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The share structure of a land-share {@see RealEstateProject} — how many
 * shares the project is divided into and the value of one share. The
 * ownership ledger (who holds which shares, transfers, payouts) is Phase 1+
 * and will hang off this row.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property int $total_shares
 * @property string $share_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'total_shares', 'share_value'])]
class LandShare extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<LandShareFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_shares' => 'integer',
            'share_value' => 'decimal:2',
        ];
    }

    protected static function newFactory(): LandShareFactory
    {
        return LandShareFactory::new();
    }
}
