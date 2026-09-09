<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;

it('converts a lead into a customer and freezes the lead', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create([
        'name' => 'Priya Prospect', 'company' => 'Initech', 'email' => 'priya@initech.test', 'stage' => 'negotiation',
    ]);
    clearTenantContext();

    $customerId = $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Priya Prospect')
        ->assertJsonPath('data.company', 'Initech')
        ->assertJsonPath('data.created_by', $manager->id)
        ->json('data.id');

    $fresh = Lead::withoutGlobalScopes()->findOrFail($lead->id);
    expect($fresh->stage->value)->toBe('converted')
        ->and($fresh->converted_customer_id)->toBe($customerId)
        ->and($fresh->converted_at)->not->toBeNull();

    expect(Customer::withoutGlobalScopes()->find($customerId)->name)->toBe('Priya Prospect');
});

it('applies overrides when converting', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create(['name' => 'Raw Name', 'email' => 'raw@x.test']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [
        'name' => 'Clean Name', 'type' => 'individual',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Clean Name')
        ->assertJsonPath('data.type', 'individual')
        ->assertJsonPath('data.email', 'raw@x.test');
});

it('will not convert a lead twice', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])->assertCreated();
    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])->assertUnprocessable();

    expect(Customer::withoutGlobalScopes()->count())->toBe(1);
});

it('will not move a converted lead\'s stage', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])->assertCreated();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/leads/{$lead->id}/stage", ['stage' => 'contacted'])
        ->assertUnprocessable()->assertJsonValidationErrors('stage');
});

it('forbids staff without lead.convert from converting', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $lead = Lead::factory()->forTenant($tenant)->create(['created_by' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])->assertForbidden();
});
