<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Models\Tenant;

function bookingPayload(Tenant $tenant, array $overrides = []): array
{
    $t1 = Traveller::factory()->forTenant($tenant)->create();
    $t2 = Traveller::factory()->forTenant($tenant)->create();

    return array_merge([
        'type' => 'air_ticket',
        'title' => 'DAC–BKK return',
        'airline' => 'Biman Bangladesh',
        'origin' => 'DAC',
        'destination' => 'BKK',
        'depart_on' => '2026-08-01',
        'return_on' => '2026-08-10',
        'cost_amount' => '82000',
        'sell_amount' => '90000',
        'commission_amount' => '2500',
        'passengers' => [
            ['traveller_id' => $t1->id, 'baggage' => '30kg'],
            ['traveller_id' => $t2->id],
        ],
        'segments' => [
            ['flight_number' => 'BG388', 'from_airport' => 'DAC', 'to_airport' => 'BKK', 'depart_at' => '2026-08-01 09:30:00'],
        ],
    ], $overrides);
}

it('creates an air-ticket booking with passengers and flight segments', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $body = $this->postJson('/api/v1/bookings', bookingPayload($tenant))
        ->assertCreated()
        ->assertJsonPath('data.status', 'quoted')
        ->assertJsonPath('data.reference', 'BKG-000001')
        ->assertJsonPath('data.profit.gross_profit', '10500.00')
        ->assertJsonCount(2, 'data.passengers')
        ->assertJsonCount(1, 'data.segments')
        ->json('data');

    $this->postJson("/api/v1/bookings/{$body['id']}/issue")->assertUnprocessable(); // no PNR
    $this->postJson("/api/v1/bookings/{$body['id']}/issue", ['pnr' => 'BQ7K2P'])
        ->assertOk()->assertJsonPath('data.status', 'ticketed')->assertJsonPath('data.pnr', 'BQ7K2P');

    // no longer editable once issued
    $this->putJson("/api/v1/bookings/{$body['id']}", bookingPayload($tenant, ['title' => 'Changed']))
        ->assertUnprocessable();
});

it('creates a hotel booking with a stay and derives the nights', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $this->postJson('/api/v1/bookings', [
        'type' => 'hotel',
        'title' => 'Bangkok 4 nights',
        'sell_amount' => '32000',
        'cost_amount' => '26000',
        'hotel_stays' => [[
            'hotel_name' => 'Grand Bangkok', 'city' => 'Bangkok', 'country' => 'Thailand',
            'check_in' => '2026-08-01', 'check_out' => '2026-08-05', 'room_type' => 'Twin', 'guests' => 2,
            'board_basis' => 'breakfast',
        ]],
    ])->assertCreated()
        ->assertJsonPath('data.type', 'hotel')
        ->assertJsonPath('data.hotel_stays.0.nights', 4)
        ->assertJsonPath('data.hotel_stays.0.board_basis', 'breakfast');
});

it('creates an umrah package with itinerary and confirms it without a PNR', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', [
        'type' => 'umrah',
        'title' => 'Umrah 14N',
        'sell_amount' => '245000',
        'cost_amount' => '210000',
        'itinerary' => [
            ['title' => 'Arrive Jeddah, transfer Makkah', 'city' => 'Makkah'],
            ['title' => 'Transfer to Madinah', 'city' => 'Madinah'],
        ],
    ])->assertCreated()
        ->assertJsonPath('data.itinerary.0.day_number', 1)
        ->assertJsonPath('data.itinerary.1.day_number', 2)
        ->json('data.id');

    $this->postJson("/api/v1/bookings/{$id}/issue")->assertOk()->assertJsonPath('data.status', 'confirmed');
});

it('refunds no more than the sell amount', function (): void {
    $tenant = makeIndustryTenant('travel');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();
    $this->actingAs($owner, 'sanctum');

    $id = $this->postJson('/api/v1/bookings', bookingPayload($tenant))->json('data.id');
    $this->postJson("/api/v1/bookings/{$id}/issue", ['pnr' => 'AAA111'])->assertOk();

    $this->postJson("/api/v1/bookings/{$id}/refund", ['amount' => '90000.01', 'refunded_on' => '2026-08-02'])
        ->assertUnprocessable();
    $this->postJson("/api/v1/bookings/{$id}/refund", ['amount' => '90000.00', 'refunded_on' => '2026-08-02'])
        ->assertOk()->assertJsonPath('data.status', 'refunded')->assertJsonPath('data.refund_amount', '90000.00');
});

it('rejects a passenger from another tenant', function (): void {
    $other = makeIndustryTenant('travel', ['slug' => 'bk-o']);
    $foreign = Traveller::factory()->forTenant($other)->create();
    $tenant = makeIndustryTenant('travel', ['slug' => 'bk-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson('/api/v1/bookings', [
        'title' => 'X', 'type' => 'air_ticket', 'sell_amount' => '1', 'cost_amount' => '1',
        'passengers' => [['traveller_id' => $foreign->id]],
    ])->assertUnprocessable();
});

it('404s on another tenant\'s booking', function (): void {
    $tenantA = makeIndustryTenant('travel', ['slug' => 'bk-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeIndustryTenant('travel', ['slug' => 'bk-iso-b']);
    $foreign = Booking::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/bookings/{$foreign->id}")->assertNotFound();
});
