<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        // Rate limiter state lives in the cache; keep every test isolated.
        cache()->flush();
        clearTenantContext();
    })
    ->in('Feature', 'Unit');

/**
 * Create a tenant with its role set provisioned.
 */
function makeTenant(array $attributes = []): Tenant
{
    $tenant = Tenant::factory()->create($attributes);
    app(ProvisionTenantRbac::class)->handle($tenant);

    return $tenant;
}

/**
 * Create a user in the given tenant with an optional role, and bind that tenant
 * as the active context (mirrors what ResolveTenant does per request).
 */
function makeUser(Tenant $tenant, ?string $role = null): User
{
    $user = User::factory()->forTenant($tenant)->create();

    app(TenantContext::class)->set($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());

    if ($role !== null) {
        $user->assignRole($role);
    }

    return $user->fresh(['tenant', 'roles']);
}

/**
 * Create a tenant in a given industry (defaults to travel) with its roles
 * provisioned — for the industry-gated modules.
 */
function makeIndustryTenant(string $industry = 'travel', array $attributes = []): Tenant
{
    return makeTenant([...$attributes, 'industry' => $industry]);
}

/**
 * Forget any bound tenant / team id. Call between assertions that switch tenants.
 */
function clearTenantContext(): void
{
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
}

expect()->extend('toBeOne', fn () => $this->toBe(1));
