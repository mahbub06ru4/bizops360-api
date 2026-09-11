<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Models\Booking;

it('raises a Finance invoice for a ticketed booking and links the two', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create(['name' => 'Globex Travel']);
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'air_ticket',
        'title' => 'DAC-BKK return',
        'customer_id' => $customer->id,
        'sell_amount' => '120000',
        'cost_amount' => '95000',
        'pnr' => 'ABC123',
    ])->json('data.id');

    $this->postJson("/api/v1/bookings/{$id}/issue")->assertOk()
        ->assertJsonPath('data.status', 'ticketed');

    $invoice = $this->postJson("/api/v1/bookings/{$id}/invoice", [
        'issue_date' => '2026-07-01', 'due_date' => '2026-07-15',
    ])->assertCreated()
        ->assertJsonPath('data.amount', '120000.00')
        ->assertJsonPath('data.customer_id', $customer->id)
        ->assertJsonPath('data.booking.id', $id)
        ->assertJsonPath('data.booking.reference', fn ($reference) => is_string($reference))
        ->json('data');

    expect(Invoice::query()->count())->toBe(1);
    $this->getJson("/api/v1/bookings/{$id}")->assertJsonPath('data.invoice_id', $invoice['id']);

    // second attempt is blocked — a booking can only be invoiced once
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

it('refuses to invoice a booking that is not yet ticketed', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    $customer = Customer::factory()->forTenant($tenant)->create();
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    // still quoted — never issued
    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'air_ticket',
        'title' => 'DAC-BKK return',
        'customer_id' => $customer->id,
        'sell_amount' => '120000',
        'cost_amount' => '95000',
        'pnr' => 'ABC123',
    ])->json('data.id');

    $this->postJson("/api/v1/bookings/{$id}/invoice")->assertUnprocessable();

    expect(Invoice::query()->count())->toBe(0);
});

it('404s when raising an invoice for another tenant booking', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $otherTenant = makeIndustryTenant('travel');
    $otherCustomer = Customer::factory()->forTenant($otherTenant)->create();
    $otherBooking = Booking::factory()->forTenant($otherTenant)->status(BookingStatus::Ticketed)->create([
        'customer_id' => $otherCustomer->id,
    ]);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')
        ->postJson("/api/v1/bookings/{$otherBooking->id}/invoice")
        ->assertNotFound();
});

it('does not let staff raise an invoice', function (): void {
    $tenant = makeIndustryTenant('travel');
    $staff = makeUser($tenant, 'staff');
    $customer = Customer::factory()->forTenant($tenant)->create();
    $booking = Booking::factory()->forTenant($tenant)->status(BookingStatus::Ticketed)->create(['customer_id' => $customer->id, 'created_by' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson("/api/v1/bookings/{$booking->id}/invoice")->assertForbidden();
});
