<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Modules\Organization\Database\Factories\TeamFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A named group of employees within a tenant, optionally led by one of them.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int|null $lead_employee_id
 * @property string $name
 * @property string|null $description
 */
#[Fillable(['lead_employee_id', 'name', 'description'])]
class Team extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /** @return BelongsTo<Employee, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'lead_employee_id');
    }

    /** @return BelongsToMany<Employee, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)->withTimestamps();
    }

    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }
}
