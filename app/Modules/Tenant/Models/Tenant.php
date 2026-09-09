<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Models;

use App\Models\User;
use App\Modules\Tenant\Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A tenant is one customer company on the platform. Every other business table
 * carries a `tenant_id` pointing here.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $industry
 * @property string|null $legal_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $timezone
 * @property string $currency
 */
#[Fillable(['name', 'slug', 'industry', 'legal_name', 'email', 'phone', 'address', 'timezone', 'currency'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
