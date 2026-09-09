<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;

it('assembles a customer\'s full history', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create(['name' => 'Origin Co', 'stage' => 'negotiation']);
    clearTenantContext();

    $customerId = $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/convert", [])
        ->assertCreated()->json('data.id');

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/customers/{$customerId}/contacts", ['name' => 'Contact A'])->assertCreated();
    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/customers/{$customerId}/notes", ['body' => 'Kicked off onboarding'])->assertCreated();
    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/customers/{$customerId}/follow-ups", ['due_at' => '2026-08-01 09:00:00'])->assertCreated();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/customers/{$customerId}/history")
        ->assertOk()
        ->assertJsonPath('data.customer.id', $customerId)
        ->assertJsonPath('data.source_lead.name', 'Origin Co')
        ->assertJsonCount(1, 'data.contacts')
        ->assertJsonCount(1, 'data.follow_ups')
        ->assertJsonCount(3, 'data.activities');
});

it('404s history for another tenant\'s customer', function (): void {
    $tenantA = makeTenant(['slug' => 'hist-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'hist-b']);
    $foreign = Customer::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/customers/{$foreign->id}/history")->assertNotFound();
});

it('lets an involved staff member view history but not an outsider', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $outsider = makeUser($tenant, 'staff');
    $customer = Customer::factory()->forTenant($tenant)->create(['created_by' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson("/api/v1/customers/{$customer->id}/history")->assertOk();
    $this->actingAs($outsider, 'sanctum')->getJson("/api/v1/customers/{$customer->id}/history")->assertForbidden();
});
