<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\Traveller;

it('lets staff do data entry but not high-consequence actions', function (): void {
    $tenant = makeIndustryTenant('travel');
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();
    $this->actingAs($staff, 'sanctum');

    $bookingId = $this->postJson('/api/v1/bookings', [
        'type' => 'air_ticket', 'title' => 'DAC-CGP', 'sell_amount' => '5000', 'cost_amount' => '4000', 'pnr' => 'PP1',
    ])->assertCreated()->json('data.id');

    $this->postJson("/api/v1/bookings/{$bookingId}/issue", ['pnr' => 'PP1'])->assertForbidden();
    $this->postJson("/api/v1/bookings/{$bookingId}/cancel")->assertForbidden();
    $this->postJson("/api/v1/bookings/{$bookingId}/refund", ['amount' => '10', 'refunded_on' => '2026-07-01'])->assertForbidden();
    $this->deleteJson("/api/v1/bookings/{$bookingId}")->assertForbidden();
});

it('lets a manager issue and cancel but not refund or delete', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'air_ticket', 'title' => 'DAC-CGP', 'sell_amount' => '5000', 'cost_amount' => '4000', 'pnr' => 'PP1',
    ])->json('data.id');

    $this->postJson("/api/v1/bookings/{$id}/issue")->assertOk();
    $this->postJson("/api/v1/bookings/{$id}/refund", ['amount' => '10', 'refunded_on' => '2026-07-01'])->assertForbidden();
    $this->deleteJson("/api/v1/bookings/{$id}")->assertForbidden();
    $this->postJson("/api/v1/bookings/{$id}/cancel")->assertOk();
});

it('grants the owner the full travel surface', function (): void {
    $tenant = makeIndustryTenant('travel');
    $owner = makeUser($tenant, 'owner');
    $traveller = Traveller::factory()->forTenant($tenant)->create();
    $booking = Booking::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/travellers/{$traveller->id}")->assertNoContent();
    $this->actingAs($owner, 'sanctum')
        ->postJson("/api/v1/bookings/{$booking->id}/refund", ['amount' => '10', 'refunded_on' => '2026-07-01'])
        ->assertOk();
});
