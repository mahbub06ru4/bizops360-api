<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Invoice;

it('creates, sends, updates and voids an invoice', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    $customer = Customer::factory()->forTenant($tenant)->create(['name' => 'Globex']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id,
        'issue_date' => '2026-06-01',
        'due_date' => '2026-06-15',
        'amount' => '1000.00',
    ])->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.number', 'INV-000001')
        ->assertJsonPath('data.customer_name', 'Globex')
        ->assertJsonPath('data.amount_due', '1000.00')
        ->json('data.id');

    $this->putJson("/api/v1/invoices/{$id}", [
        'customer_id' => $customer->id,
        'issue_date' => '2026-06-01',
        'amount' => '1200.00',
    ])->assertOk()->assertJsonPath('data.amount', '1200.00');

    $this->postJson("/api/v1/invoices/{$id}/send")->assertOk()->assertJsonPath('data.status', 'sent');

    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/invoices/{$id}/void")->assertOk()->assertJsonPath('data.status', 'void');
});

it('blocks editing once a payment is recorded', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');
    $id = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '500.00',
    ])->json('data.id');
    $this->postJson("/api/v1/invoices/{$id}/payments", ['amount' => '100.00', 'paid_on' => '2026-06-02'])->assertCreated();

    $this->putJson("/api/v1/invoices/{$id}", [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '900.00',
    ])->assertUnprocessable();
});

it('only deletes a draft invoice', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');
    $draft = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '500.00',
    ])->json('data.id');
    $sent = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '500.00',
    ])->json('data.id');
    $this->postJson("/api/v1/invoices/{$sent}/send")->assertOk();

    $this->actingAs($owner, 'sanctum');
    $this->deleteJson("/api/v1/invoices/{$sent}")->assertUnprocessable();
    $this->deleteJson("/api/v1/invoices/{$draft}")->assertNoContent();
});

it('rejects a customer from another tenant', function (): void {
    $other = makeTenant(['slug' => 'inv-o']);
    $foreign = Customer::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'inv-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/invoices', [
        'customer_id' => $foreign->id, 'issue_date' => '2026-06-01', 'amount' => '100.00',
    ])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
});

it('404s on another tenant\'s invoice and numbers per tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'inv-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $customerA = Customer::factory()->forTenant($tenantA)->create();
    $tenantB = makeTenant(['slug' => 'inv-iso-b']);
    $foreign = Invoice::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');
    $this->getJson("/api/v1/invoices/{$foreign->id}")->assertNotFound();
    $this->postJson('/api/v1/invoices', [
        'customer_id' => $customerA->id, 'issue_date' => '2026-06-01', 'amount' => '100.00',
    ])->assertCreated()->assertJsonPath('data.number', 'INV-000001');
});
