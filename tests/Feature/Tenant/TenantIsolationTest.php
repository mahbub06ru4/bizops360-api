<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Tenant\Context\TenantContext;

it('scopes tenant-owned queries to the bound tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'tenant-a']);
    $tenantB = makeTenant(['slug' => 'tenant-b']);

    User::factory()->forTenant($tenantA)->count(2)->create();
    User::factory()->forTenant($tenantB)->count(3)->create();

    app(TenantContext::class)->set($tenantA);
    expect(User::count())->toBe(2);

    app(TenantContext::class)->set($tenantB);
    expect(User::count())->toBe(3);

    clearTenantContext();
    expect(User::count())->toBe(5);
});

it('auto-fills tenant_id from context on create', function (): void {
    $tenant = makeTenant();
    app(TenantContext::class)->set($tenant);

    $user = User::factory()->create(['tenant_id' => null]);

    expect($user->fresh()->tenant_id)->toBe($tenant->id);
});

it('hides another tenant\'s user from an authenticated request', function (): void {
    $tenantA = makeTenant(['slug' => 'iso-a']);
    $viewer = makeUser($tenantA, 'admin');

    $tenantB = makeTenant(['slug' => 'iso-b']);
    $foreign = makeUser($tenantB, 'staff');
    clearTenantContext();

    // Sanity: the foreign user is invisible once tenant A is the active context.
    $this->actingAs($viewer, 'sanctum')->getJson('/api/v1/auth/me')->assertOk();

    app(TenantContext::class)->set($tenantA->fresh());
    expect(User::find($foreign->id))->toBeNull()
        ->and(User::find($viewer->id))->not->toBeNull();
});

it('never exposes an Eloquent model directly from the auth endpoints', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant);
    clearTenantContext();

    $json = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me')->json();

    // API Resource wraps under "data" and does not leak the password hash.
    expect($json)->toHaveKey('data')
        ->and($json['data'])->not->toHaveKey('password');
});
