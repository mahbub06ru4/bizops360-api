<?php

declare(strict_types=1);

it('returns platform defaults until an office location is configured', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/office-location')
        ->assertOk()
        ->assertJsonPath('data.label', 'Head Office')
        ->assertJsonPath('data.radius_meters', 500)
        ->assertJsonPath('data.start_time', '09:00')
        ->assertJsonPath('data.end_time', '17:00');
});

it('lets a manager set the office location', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/office-location', [
        'label' => 'Dhaka HQ',
        'latitude' => 23.780636,
        'longitude' => 90.279016,
        'radius_meters' => 300,
        'start_time' => '10:00',
        'end_time' => '18:00',
    ])->assertOk()
        ->assertJsonPath('data.label', 'Dhaka HQ')
        ->assertJsonPath('data.latitude', 23.780636)
        ->assertJsonPath('data.longitude', 90.279016)
        ->assertJsonPath('data.radius_meters', 300)
        ->assertJsonPath('data.start_time', '10:00')
        ->assertJsonPath('data.end_time', '18:00');

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/office-location')
        ->assertOk()
        ->assertJsonPath('data.label', 'Dhaka HQ');
});

it('lets staff view but not change the office location', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/office-location')->assertOk();
    $this->actingAs($staff, 'sanctum')->putJson('/api/v1/office-location', [
        'label' => 'Somewhere',
        'latitude' => 23.7,
        'longitude' => 90.4,
        'radius_meters' => 100,
        'start_time' => '09:00',
        'end_time' => '17:00',
    ])->assertForbidden();
});

it('validates the office location payload', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson('/api/v1/office-location', [
        'label' => 'Dhaka HQ',
        'latitude' => 200,
        'longitude' => 90.0,
        'radius_meters' => 300,
        'start_time' => '18:00',
        'end_time' => '10:00',
    ])->assertUnprocessable()->assertJsonValidationErrors(['latitude', 'end_time']);
});

it('rejects anonymous access', function (): void {
    $this->getJson('/api/v1/office-location')->assertUnauthorized();
});
