<?php

declare(strict_types=1);

use App\Models\User;

it('registers a tenant with its owner and returns a token', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'company_name' => 'Acme Tours',
        'industry' => 'travel',
        'owner_name' => 'Dana Owner',
        'owner_email' => 'dana@acme.test',
        'owner_password' => 'password1234',
        'owner_password_confirmation' => 'password1234',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'dana@acme.test')
        ->assertJsonPath('data.tenant.name', 'Acme Tours')
        ->assertJsonPath('data.roles.0', 'owner')
        ->assertJsonStructure(['data' => ['id', 'tenant' => ['id', 'slug']], 'token']);

    $user = User::where('email', 'dana@acme.test')->firstOrFail();
    expect($user->tenant_id)->not->toBeNull()
        ->and($user->tokens()->count())->toBe(1);
});

it('rejects registration with a duplicate owner email', function (): void {
    $tenant = makeTenant();
    User::factory()->forTenant($tenant)->create(['email' => 'taken@acme.test']);

    $this->postJson('/api/v1/auth/register', [
        'company_name' => 'Second Co',
        'owner_name' => 'Someone',
        'owner_email' => 'taken@acme.test',
        'owner_password' => 'password1234',
        'owner_password_confirmation' => 'password1234',
    ])->assertUnprocessable()->assertJsonValidationErrors('owner_email');
});

it('validates required registration fields', function (): void {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_name', 'owner_name', 'owner_email', 'owner_password']);
});

it('logs in with valid credentials and issues a token scoped to permissions', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant, 'admin');
    clearTenantContext();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'iphone',
    ]);

    $response->assertOk()->assertJsonPath('data.id', $user->id);
    expect($user->tokens()->first()->abilities)->toContain('tenant.settings.view');
});

it('rejects login with a wrong password', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant);
    clearTenantContext();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('returns the authenticated user from /auth/me', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.tenant.id', $tenant->id)
        ->assertJsonPath('data.roles.0', 'manager');
});

it('rejects /auth/me without a token', function (): void {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('revokes the current token on logout', function (): void {
    $tenant = makeTenant();
    $user = makeUser($tenant);
    clearTenantContext();

    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

    expect($user->tokens()->count())->toBe(0);
});
