<?php

declare(strict_types=1);

use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;

it('walks a lead through its lifecycle', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/leads', [
        'name' => 'Jordan Prospect',
        'company' => 'Acme',
        'email' => 'jordan@acme.test',
        'estimated_value' => 12000,
    ])->assertCreated()
        ->assertJsonPath('data.stage', 'new')
        ->assertJsonPath('data.created_by', $manager->id)
        ->json('data.id');

    $this->getJson('/api/v1/leads')->assertOk()->assertJsonPath('data.0.id', $id);

    $this->putJson("/api/v1/leads/{$id}", ['name' => 'Jordan P', 'estimated_value' => 15000])
        ->assertOk()->assertJsonPath('data.estimated_value', '15000.00');

    $this->putJson("/api/v1/leads/{$id}/stage", ['stage' => 'contacted'])
        ->assertOk()->assertJsonPath('data.stage', 'contacted');

    $this->deleteJson("/api/v1/leads/{$id}")->assertNoContent();
});

it('validates the lead and blocks a direct move to converted', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/leads', [])
        ->assertUnprocessable()->assertJsonValidationErrors('name');

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/leads/{$lead->id}/stage", ['stage' => 'converted'])
        ->assertUnprocessable()->assertJsonValidationErrors('stage');

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/leads/{$lead->id}/stage", ['stage' => 'lost'])
        ->assertUnprocessable()->assertJsonValidationErrors('lost_reason');
});

it('records a lost reason and clears it on re-open', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/leads/{$lead->id}/stage", [
        'stage' => 'lost', 'lost_reason' => 'Budget',
    ])->assertOk()->assertJsonPath('data.lost_reason', 'Budget');

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/leads/{$lead->id}/stage", ['stage' => 'contacted'])
        ->assertOk()->assertJsonPath('data.lost_reason', null);
});

it('summarises the pipeline by stage', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    Lead::factory()->forTenant($tenant)->stage(LeadStage::New)->create(['estimated_value' => 1000]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::New)->create(['estimated_value' => 500]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Negotiation)->create(['estimated_value' => 9000]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/leads/pipeline')
        ->assertOk()
        ->assertJsonPath('data.new.count', 2)
        ->assertJsonPath('data.new.value', '1500.00')
        ->assertJsonPath('data.negotiation.count', 1)
        ->assertJsonPath('data.negotiation.value', '9000.00')
        ->assertJsonPath('data.contacted.count', 0);
});

it('scopes leads to involvement unless the caller may view all', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    $staffEmployee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/leads', ['name' => 'Mine'])->assertCreated();
    Lead::factory()->forTenant($tenant)->create(['owner_employee_id' => $staffEmployee->id]);
    Lead::factory()->forTenant($tenant)->create();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/leads')->assertOk()->assertJsonPath('meta.total', 2);
    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/leads')->assertOk()->assertJsonPath('meta.total', 3);
});

it('404s on another tenant\'s lead', function (): void {
    $tenantA = makeTenant(['slug' => 'lead-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'lead-b']);
    $foreign = Lead::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/leads/{$foreign->id}")->assertNotFound();
});
