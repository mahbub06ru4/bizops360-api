<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Models\Tenant;

function makeInvoice(int $tenantId, string $amount = '1000.00'): int
{
    $customer = Customer::factory()->forTenant(Tenant::find($tenantId))->create();

    return test()->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id,
        'issue_date' => '2026-06-01',
        'amount' => $amount,
    ])->json('data.id');
}

it('records a partial then a final payment, moving the status', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = makeInvoice($tenant->id, '1000.00');

    $this->postJson("/api/v1/invoices/{$id}/payments", ['amount' => '400.00', 'paid_on' => '2026-06-05'])
        ->assertCreated();
    $this->getJson("/api/v1/invoices/{$id}")
        ->assertJsonPath('data.status', 'partial')
        ->assertJsonPath('data.amount_paid', '400.00')
        ->assertJsonPath('data.amount_due', '600.00');

    $this->postJson("/api/v1/invoices/{$id}/payments", ['amount' => '600.00', 'paid_on' => '2026-06-08'])
        ->assertCreated();
    $this->getJson("/api/v1/invoices/{$id}")
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.amount_due', '0.00');

    $this->getJson("/api/v1/invoices/{$id}/payments")->assertOk()->assertJsonCount(2, 'data');
});

it('rejects a payment that exceeds the amount due', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = makeInvoice($tenant->id, '300.00');

    $this->postJson("/api/v1/invoices/{$id}/payments", ['amount' => '300.01', 'paid_on' => '2026-06-05'])
        ->assertUnprocessable()->assertJsonValidationErrors('amount');
});

it('refunds up to the amount paid and updates the status', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();
    $this->actingAs($owner, 'sanctum');

    $id = makeInvoice($tenant->id, '500.00');
    $this->postJson("/api/v1/invoices/{$id}/payments", ['amount' => '500.00', 'paid_on' => '2026-06-05'])->assertCreated();

    $this->postJson("/api/v1/invoices/{$id}/refunds", ['amount' => '500.01', 'refunded_on' => '2026-06-10'])
        ->assertUnprocessable()->assertJsonValidationErrors('amount');

    $this->postJson("/api/v1/invoices/{$id}/refunds", ['amount' => '500.00', 'refunded_on' => '2026-06-10'])
        ->assertCreated();
    $this->getJson("/api/v1/invoices/{$id}")
        ->assertJsonPath('data.status', 'refunded')
        ->assertJsonPath('data.amount_refunded', '500.00')
        ->assertJsonPath('data.amount_due', '500.00');
});

it('will not take a payment on another tenant\'s invoice', function (): void {
    $tenantA = makeTenant(['slug' => 'pay-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'pay-iso-b']);
    $foreign = Invoice::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/invoices/{$foreign->id}/payments", ['amount' => '10.00', 'paid_on' => '2026-06-05'])
        ->assertNotFound();
});
