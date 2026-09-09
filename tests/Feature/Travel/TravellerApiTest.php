<?php

declare(strict_types=1);

use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\BookingPassenger;
use App\Modules\Industry\Travel\Models\Traveller;

it('walks a traveller through its lifecycle', function (): void {
    $tenant = makeIndustryTenant('travel');
    $staff = makeUser($tenant, 'staff');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum');

    $id = $this->postJson('/api/v1/travellers', [
        'full_name' => 'Karim Chowdhury',
        'gender' => 'male',
        'nationality' => 'Bangladeshi',
        'passport_number' => 'BD9988776',
        'passport_expiry' => '2031-05-01',
    ])->assertCreated()
        ->assertJsonPath('data.full_name', 'Karim Chowdhury')
        ->assertJsonPath('data.passport_number', 'BD9988776')
        ->json('data.id');

    $this->getJson('/api/v1/travellers')->assertOk()->assertJsonPath('data.0.id', $id);
    $this->getJson('/api/v1/travellers?q=9988')->assertOk()->assertJsonPath('meta.total', 1);

    $this->putJson("/api/v1/travellers/{$id}", [
        'full_name' => 'Karim Chowdhury', 'phone' => '+8801777000000',
    ])->assertOk()->assertJsonPath('data.phone', '+8801777000000');

    // staff cannot delete
    $this->deleteJson("/api/v1/travellers/{$id}")->assertForbidden();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/travellers/{$id}")->assertNoContent();
});

it('rejects a customer from another tenant', function (): void {
    $other = makeIndustryTenant('travel', ['slug' => 'trv-o']);
    $foreign = Customer::factory()->forTenant($other)->create();
    $tenant = makeIndustryTenant('travel', ['slug' => 'trv-m']);
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/travellers', [
        'full_name' => 'X', 'customer_id' => $foreign->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
});

it('will not delete a traveller attached to a booking', function (): void {
    $tenant = makeIndustryTenant('travel');
    $owner = makeUser($tenant, 'owner');
    $traveller = Traveller::factory()->forTenant($tenant)->create();
    $booking = Booking::factory()->forTenant($tenant)->create();
    BookingPassenger::factory()->forTenant($tenant)->create([
        'booking_id' => $booking->id, 'traveller_id' => $traveller->id,
    ]);
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/travellers/{$traveller->id}")
        ->assertUnprocessable();
});

it('404s on another tenant\'s traveller', function (): void {
    $tenantA = makeIndustryTenant('travel', ['slug' => 'trv-iso-a']);
    $staff = makeUser($tenantA, 'staff');
    $tenantB = makeIndustryTenant('travel', ['slug' => 'trv-iso-b']);
    $foreign = Traveller::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson("/api/v1/travellers/{$foreign->id}")->assertNotFound();
});
