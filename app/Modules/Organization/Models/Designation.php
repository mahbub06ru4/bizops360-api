<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Modules\Organization\Database\Factories\DesignationFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A job title / designation within a tenant, optionally scoped to a department.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $department_id
 * @property string $title
 * @property int|null $rank
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['department_id', 'title', 'rank'])]
class Designation extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<DesignationFactory> */
    use HasFactory;

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rank' => 'integer',
        ];
    }

    protected static function newFactory(): DesignationFactory
    {
        return DesignationFactory::new();
    }
}
