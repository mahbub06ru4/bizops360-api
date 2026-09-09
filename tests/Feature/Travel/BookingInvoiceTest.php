<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Industry\Travel\Models\Booking;

it('raises a Finance invoice for a booking and links the two', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create(['name' => 'Globex Travel']);
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'tour_package',
        'title' => 'Thailand 4N5D',
        'customer_id' => $customer->id,
        'sell_amount' => '120000',
        'cost_amount' => '95000',
    ])->json('data.id');

    $invoice = $this->postJson("/api/v1/bookings/{$id}/invoice", [
        'issue_date' => '2026-07-01', 'due_date' => '2026-07-15',
    ])->assertCreated()
        ->assertJsonPath('data.amount', '120000.00')
        ->assertJsonPath('data.customer_id', $customer->id)
        ->json('data');

    expect(Invoice::query()->count())->toBe(1);
    $this->getJson("/api/v1/bookings/{$id}")->assertJsonPath('data.invoice_id', $invoice['id']);

    // second attempt is blocked
    $this->postJson("/api/v1/bookings/{$id}/invoice")->assertUnprocessable();
});

it('refuses to invoice a booking without a customer', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'hotel', 'title' => 'Walk-in hotel', 'sell_amount' => '10000', 'cost_amount' => '8000',
    ])->json('data.id');

    $this->postJson("/api/v1/bookings/{$id}/invoice")->assertUnprocessable();
});

it('does not let staff raise an invoice', function (): void {
    $tenant = makeIndustryTenant('travel');
    $staff = makeUser($tenant, 'staff');
    $customer = Customer::factory()->forTenant($tenant)->create();
    $booking = Booking::factory()->forTenant($tenant)->create(['customer_id' => $customer->id, 'created_by' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/bookings/{$booking->id}/invoice")->assertForbidden();
});
