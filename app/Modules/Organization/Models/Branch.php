<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Modules\Organization\Database\Factories\BranchFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A physical or organizational branch of a tenant company.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $code
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property bool $is_head_office
 */
#[Fillable(['name', 'code', 'address', 'phone', 'email', 'is_head_office'])]
class Branch extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_head_office' => 'boolean',
        ];
    }

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }
}
