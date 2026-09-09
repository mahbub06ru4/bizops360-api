<?php

declare(strict_types=1);

use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\InvoiceStatus;
use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoicePayment;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('summarises income against expense for the tenant', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');

    Income::factory()->forTenant($tenant)->category(IncomeCategory::Other)->on('2026-06-02')->create(['amount' => '1000.00']);
    Expense::factory()->forTenant($tenant)->category(ExpenseCategory::Office)->on('2026-06-03')->create(['amount' => '400.00']);

    $invoice = Invoice::factory()->forTenant($tenant)->status(InvoiceStatus::Partial)
        ->create(['amount' => '2000.00', 'amount_paid' => '500.00', 'issue_date' => '2026-06-01', 'due_date' => '2026-05-20']);
    InvoicePayment::factory()->forInvoice($invoice)->create(['amount' => '500.00', 'paid_on' => '2026-06-04']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/finance/overview')
        ->assertOk()
        ->assertJsonPath('data.all_time.income', '1500.00')
        ->assertJsonPath('data.all_time.expense', '400.00')
        ->assertJsonPath('data.all_time.profit', '1100.00')
        ->assertJsonPath('data.receivables.outstanding_invoices', 1)
        ->assertJsonPath('data.receivables.outstanding_amount', '1500.00');
});

it('reports profit and loss for a date range', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');

    Income::factory()->forTenant($tenant)->on('2026-06-10')->create(['amount' => '3000.00']);
    Income::factory()->forTenant($tenant)->on('2026-07-10')->create(['amount' => '9999.00']);
    Expense::factory()->forTenant($tenant)->category(ExpenseCategory::Supplier)->on('2026-06-12')->create(['amount' => '1000.00']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->getJson('/api/v1/finance/profit-loss?from=2026-06-01&to=2026-06-30')
        ->assertOk()
        ->assertJsonPath('data.summary.income', '3000.00')
        ->assertJsonPath('data.summary.expense', '1000.00')
        ->assertJsonPath('data.summary.profit', '2000.00')
        ->assertJsonPath('data.expense_by_category.supplier', '1000.00');
});

it('lists outstanding invoices and customer dues', function (): void {
    Carbon::setTestNow('2026-07-01 09:00:00');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');

    Invoice::factory()->forTenant($tenant)->status(InvoiceStatus::Sent)
        ->create(['customer_name' => 'Globex', 'amount' => '1000.00', 'amount_paid' => '0.00', 'due_date' => '2026-05-01']);
    Invoice::factory()->forTenant($tenant)->status(InvoiceStatus::Partial)
        ->create(['customer_name' => 'Globex', 'amount' => '2000.00', 'amount_paid' => '500.00', 'due_date' => '2026-06-20']);
    Invoice::factory()->forTenant($tenant)->status(InvoiceStatus::Paid)
        ->create(['customer_name' => 'Initech', 'amount' => '800.00', 'amount_paid' => '800.00']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/finance/outstanding-invoices')
        ->assertOk()
        ->assertJsonPath('data.invoice_count', 2)
        ->assertJsonPath('data.total_outstanding', '2500.00');

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/finance/customer-dues')
        ->assertOk()
        ->assertJsonPath('data.customer_count', 1)
        ->assertJsonPath('data.customers.0.customer_name', 'Globex')
        ->assertJsonPath('data.customers.0.amount_due', '2500.00');
});

it('reports monthly figures for a year', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    Income::factory()->forTenant($tenant)->on('2026-03-15')->create(['amount' => '1200.00']);
    Expense::factory()->forTenant($tenant)->on('2026-03-20')->create(['amount' => '200.00']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/finance/monthly?year=2026')
        ->assertOk()
        ->assertJsonPath('data.months.2.profit', '1000.00')
        ->assertJsonPath('data.summary.profit', '1000.00');
});

it('forbids staff from the finance reports', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    foreach (['overview', 'profit-loss', 'outstanding-invoices', 'customer-dues', 'monthly'] as $report) {
        $this->actingAs($staff, 'sanctum')->getJson("/api/v1/finance/{$report}")->assertForbidden();
    }
});

it('excludes another tenant\'s rows from the overview', function (): void {
    $tenantA = makeTenant(['slug' => 'rep-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'rep-b']);
    Income::factory()->forTenant($tenantB)->create(['amount' => '5000.00']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/finance/overview')
        ->assertOk()->assertJsonPath('data.all_time.income', '0.00');
});
