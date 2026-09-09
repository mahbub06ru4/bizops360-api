<?php

declare(strict_types=1);

use App\Models\User;

it('adds a user with roles to the current tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/users', [
            'name' => 'New Hire',
            'email' => 'new@acme.test',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'roles' => ['manager'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.roles.0', 'manager');

    $created = User::where('email', 'new@acme.test')->firstOrFail();
    expect($created->tenant_id)->toBe($tenant->id);
});

it('replaces a user\'s roles through the roles endpoint', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $target = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/users/{$target->id}/roles", ['roles' => ['manager']])
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'manager');
});

it('refuses to strip the last owner', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/users/{$owner->id}/roles", ['roles' => ['admin']])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('roles');
});

it('refuses to let a user delete their own account', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/users/{$owner->id}")
        ->assertUnprocessable();
});

it('deletes another user in the tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $victim = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->deleteJson("/api/v1/users/{$victim->id}")
        ->assertNoContent();

    expect(User::withoutGlobalScopes()->find($victim->id))->toBeNull();
});

it('lets a manager list users but not create them', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/users')->assertOk();

    $this->actingAs($manager, 'sanctum')
        ->postJson('/api/v1/users', [
            'name' => 'X',
            'email' => 'x@acme.test',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ])
        ->assertForbidden();
});

it('isolates tenant users from each other', function (): void {
    $tenantA = makeTenant(['slug' => 'user-iso-a']);
    $owner = makeUser($tenantA, 'owner');

    $tenantB = makeTenant(['slug' => 'user-iso-b']);
    $foreign = makeUser($tenantB, 'staff');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/users/{$foreign->id}")->assertNotFound();
    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/users/{$foreign->id}/roles", ['roles' => ['manager']])
        ->assertNotFound();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/users/{$foreign->id}")->assertNotFound();
});
