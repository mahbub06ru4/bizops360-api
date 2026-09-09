<?php

declare(strict_types=1);

use App\Modules\Organization\Models\Employee;
use App\Modules\Organization\Models\Team;

it('creates a team, sets its members and deletes it', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $lead = Employee::factory()->forTenant($tenant)->create();
    $members = Employee::factory()->forTenant($tenant)->count(2)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/teams', [
        'name' => 'Field Ops',
        'lead_employee_id' => $lead->id,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Field Ops')
        ->assertJsonPath('data.lead_employee_id', $lead->id)
        ->json('data.id');

    $this->putJson("/api/v1/teams/{$id}/members", ['members' => $members->pluck('id')->all()])
        ->assertOk()
        ->assertJsonPath('data.members_count', 2);

    $this->getJson("/api/v1/teams/{$id}")
        ->assertOk()
        ->assertJsonCount(2, 'data.members');

    $this->deleteJson("/api/v1/teams/{$id}")->assertNoContent();
    expect(Team::withoutGlobalScopes()->find($id))->toBeNull();
});

it('rejects a member from another tenant', function (): void {
    $other = makeTenant(['slug' => 'team-other']);
    $foreign = Employee::factory()->forTenant($other)->create();

    $tenant = makeTenant(['slug' => 'team-mine']);
    $owner = makeUser($tenant, 'owner');
    $team = Team::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/teams/{$team->id}/members", ['members' => [$foreign->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('members.0');
});

it('rejects a duplicate team name within a tenant', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    Team::factory()->forTenant($tenant)->create(['name' => 'Ops']);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->postJson('/api/v1/teams', ['name' => 'Ops'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('lets staff view teams but not create them', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/teams')->assertOk();
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/teams', ['name' => 'X'])->assertForbidden();
});

it('404s on another tenant\'s team', function (): void {
    $tenantA = makeTenant(['slug' => 'team-iso-a']);
    $owner = makeUser($tenantA, 'owner');

    $tenantB = makeTenant(['slug' => 'team-iso-b']);
    $foreign = Team::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/teams/{$foreign->id}")->assertNotFound();
    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/teams/{$foreign->id}/members", ['members' => []])
        ->assertNotFound();
});
