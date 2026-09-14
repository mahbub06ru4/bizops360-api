<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Modules\Industry\RealEstate\Database\Factories\LandRecordFactory;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectResource;
use App\Modules\Industry\RealEstate\Policies\LandRecordPolicy;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The legal land record backing a `land_share`/`plot` {@see RealEstateProject}
 * — mouza, JL/khatian/dag numbers. Admin-only visibility: gated by
 * {@see LandRecordPolicy}, never
 * exposed through {@see ProjectResource}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property string|null $mouza
 * @property string|null $jl_no
 * @property string|null $khatian_no
 * @property string|null $dag_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'mouza', 'jl_no', 'khatian_no', 'dag_no'])]
class LandRecord extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<LandRecordFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    protected static function newFactory(): LandRecordFactory
    {
        return LandRecordFactory::new();
    }
}
