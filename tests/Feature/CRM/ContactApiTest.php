<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;

it('adds contacts to a lead and to a customer', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/contacts", [
        'name' => 'Sam Buyer', 'title' => 'CTO', 'email' => 'sam@x.test', 'is_primary' => true,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Sam Buyer')
        ->assertJsonPath('data.contactable_type', 'Lead')
        ->assertJsonPath('data.is_primary', true);

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/customers/{$customer->id}/contacts", [
        'name' => 'Ada Payer',
    ])->assertCreated()->assertJsonPath('data.contactable_type', 'Customer');

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/leads/{$lead->id}/contacts")
        ->assertOk()->assertJsonPath('meta.total', 1);
});

it('demotes the existing primary contact when a new primary is added', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $first = $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/contacts", [
        'name' => 'First', 'is_primary' => true,
    ])->json('data.id');

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/contacts", [
        'name' => 'Second', 'is_primary' => true,
    ])->assertCreated();

    expect(Contact::withoutGlobalScopes()->find($first)->is_primary)->toBeFalse()
        ->and(Contact::withoutGlobalScopes()->where('name', 'Second')->first()->is_primary)->toBeTrue();
});

it('updates and deletes a contact through its parent\'s policy', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    $contact = Contact::factory()->forTenant($tenant)->attachedTo($lead)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/contacts/{$contact->id}", ['name' => 'Renamed'])
        ->assertOk()->assertJsonPath('data.name', 'Renamed');

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/contacts/{$contact->id}")->assertNoContent();
});

it('forbids an uninvolved staff member from adding a contact', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/contacts", ['name' => 'X'])
        ->assertForbidden();
});

it('removes a lead\'s contacts when the lead is deleted', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    Contact::factory()->forTenant($tenant)->attachedTo($lead)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/leads/{$lead->id}")->assertNoContent();

    expect(Contact::withoutGlobalScopes()->count())->toBe(0);
});

it('404s on another tenant\'s contact', function (): void {
    $tenantA = makeTenant(['slug' => 'con-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'con-b']);
    $lead = Lead::factory()->forTenant($tenantB)->create();
    $foreign = Contact::factory()->forTenant($tenantB)->attachedTo($lead)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->putJson("/api/v1/contacts/{$foreign->id}", ['name' => 'X'])
        ->assertNotFound();
});
