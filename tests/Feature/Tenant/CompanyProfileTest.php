<?php

declare(strict_types=1);

$validProfile = [
    'name' => 'Renamed Co',
    'legal_name' => 'Renamed Co Ltd',
    'industry' => 'real_estate',
    'email' => 'hq@renamed.test',
    'phone' => '+1 555 0100',
    'address' => '1 Main St',
    'timezone' => 'Europe/London',
    'currency' => 'GBP',
];

it('shows and updates the company profile', function () use ($validProfile): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->getJson('/api/v1/company')
        ->assertOk()
        ->assertJsonPath('data.slug', $tenant->slug)
        ->assertJsonPath('data.currency', 'USD');

    $this->actingAs($owner, 'sanctum')
        ->putJson('/api/v1/company', $validProfile)
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed Co')
        ->assertJsonPath('data.currency', 'GBP')
        ->assertJsonPath('data.timezone', 'Europe/London');

    expect($tenant->fresh()->legal_name)->toBe('Renamed Co Ltd');
});

it('never lets the slug be changed through the profile endpoint', function () use ($validProfile): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson('/api/v1/company', [...$validProfile, 'slug' => 'hacked-slug'])
        ->assertOk();

    expect($tenant->fresh()->slug)->toBe($tenant->slug);
});

it('lets a manager view but not update the company', function () use ($validProfile): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/company')->assertOk();
    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/company', $validProfile)->assertForbidden();
});

it('forbids a staff user from the company profile', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/company')->assertForbidden();
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/company')->assertUnauthorized();
});

it('validates the industry value', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson('/api/v1/company', [
            'name' => 'X',
            'industry' => 'banking',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('industry');
});
