<?php

declare(strict_types=1);

use App\Modules\HR\Models\Holiday;

it('creates, lists, updates and deletes a holiday', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/holidays', ['name' => 'New Year', 'date' => '2026-01-01'])
        ->assertCreated()
        ->assertJsonPath('data.date', '2026-01-01')
        ->json('data.id');

    $this->getJson('/api/v1/holidays')->assertOk()->assertJsonPath('data.0.id', $id);

    $this->putJson("/api/v1/holidays/{$id}", ['name' => 'NYD', 'date' => '2026-01-01', 'is_recurring' => true])
        ->assertOk()
        ->assertJsonPath('data.is_recurring', true);

    $this->deleteJson("/api/v1/holidays/{$id}")->assertNoContent();
});

it('rejects a duplicate holiday date in the same tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Holiday::factory()->forTenant($tenant)->create(['date' => '2026-05-01']);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/holidays', ['name' => 'Labour Day', 'date' => '2026-05-01'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('date');
});

it('lets staff view but not manage holidays', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/holidays')->assertOk();
    $this->actingAs($staff, 'sanctum')
        ->postJson('/api/v1/holidays', ['name' => 'X', 'date' => '2026-03-03'])
        ->assertForbidden();
});

it('404s on another tenant\'s holiday', function (): void {
    $tenantA = makeTenant(['slug' => 'hol-a']);
    $owner = makeUser($tenantA, 'owner');
    $tenantB = makeTenant(['slug' => 'hol-b']);
    $foreign = Holiday::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/holidays/{$foreign->id}")->assertNotFound();
});
