<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\Invoice;

it('denies staff every finance endpoint', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    Income::factory()->forTenant($tenant)->create();
    Expense::factory()->forTenant($tenant)->create();
    Invoice::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/incomes')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/expenses')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/invoices')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/incomes', [
        'amount' => '10.00', 'received_on' => '2026-06-01',
    ])->assertForbidden();
});

it('lets a manager record but not delete, and not void or refund', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $incomeId = $this->postJson('/api/v1/incomes', [
        'amount' => '50.00', 'received_on' => '2026-06-01',
    ])->assertCreated()->json('data.id');
    $this->deleteJson("/api/v1/incomes/{$incomeId}")->assertForbidden();

    $invoiceId = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '500.00',
    ])->assertCreated()->json('data.id');
    $this->postJson("/api/v1/invoices/{$invoiceId}/send")->assertOk();
    $this->postJson("/api/v1/invoices/{$invoiceId}/payments", ['amount' => '500.00', 'paid_on' => '2026-06-02'])->assertCreated();

    $this->postJson("/api/v1/invoices/{$invoiceId}/refunds", ['amount' => '100.00', 'refunded_on' => '2026-06-03'])
        ->assertForbidden();
    $this->postJson("/api/v1/invoices/{$invoiceId}/void")->assertForbidden();
    $this->deleteJson("/api/v1/invoices/{$invoiceId}")->assertForbidden();
});

it('grants the owner the full finance surface', function (): void {
    $tenant = makeTenant();
    $owner = makeUser($tenant, 'owner');
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum');

    $invoiceId = $this->postJson('/api/v1/invoices', [
        'customer_id' => $customer->id, 'issue_date' => '2026-06-01', 'amount' => '500.00',
    ])->assertCreated()->json('data.id');
    $this->postJson("/api/v1/invoices/{$invoiceId}/payments", ['amount' => '500.00', 'paid_on' => '2026-06-02'])->assertCreated();
    $this->postJson("/api/v1/invoices/{$invoiceId}/refunds", ['amount' => '500.00', 'refunded_on' => '2026-06-03'])->assertCreated();
    $this->postJson("/api/v1/invoices/{$invoiceId}/void")->assertOk();
    $this->getJson('/api/v1/finance/overview')->assertOk();
});
