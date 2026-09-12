<?php

declare(strict_types=1);

use App\Modules\Audit\Models\Activity;
use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Tenant\Context\TenantContext;

it('logs create and update activity on a business model, tenant-scoped', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create();

    $invoice = Invoice::factory()->forTenant($tenant)->create([
        'customer_id' => $customer->id,
        'amount' => '500.00',
    ]);
    $invoice->update(['amount' => '750.00']);

    $invoiceActivity = Activity::where('log_name', 'invoices');

    expect((clone $invoiceActivity)->where('event', 'created')->count())->toBe(1)
        ->and((clone $invoiceActivity)->where('event', 'updated')->first()->attribute_changes['attributes']['amount'])->toBe('750.00')
        ->and(Activity::where('log_name', 'customers')->where('event', 'created')->count())->toBe(1);

    clearTenantContext();
});

it('scopes activity to the owning tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'audit-a']);
    app(TenantContext::class)->set($tenantA);
    Customer::factory()->forTenant($tenantA)->create();

    $tenantB = makeTenant(['slug' => 'audit-b']);
    app(TenantContext::class)->set($tenantB);
    Customer::factory()->forTenant($tenantB)->create();

    app(TenantContext::class)->set($tenantA);
    expect(Activity::count())->toBe(1);

    clearTenantContext();
    expect(Activity::count())->toBe(2);
});

it('lists activity through the API, gated by audit.view', function (): void {
    $tenant = makeTenant();
    $admin = makeUser($tenant, 'admin');
    $staff = makeUser($tenant, 'staff');
    Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/activity')
        ->assertOk()
        ->assertJsonPath('data.0.event', 'created')
        ->assertJsonPath('data.0.log_name', 'customers');

    $this->actingAs($staff, 'sanctum')
        ->getJson('/api/v1/activity')
        ->assertForbidden();
});
