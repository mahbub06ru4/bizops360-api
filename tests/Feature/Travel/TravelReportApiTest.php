<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\BookingType;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Industry\Travel\Models\VisaApplication;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('summarises the travel desk for the tenant', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');

    VisaApplication::factory()->forTenant($tenant)->stage(VisaStage::Submitted)->create(['submitted_on' => '2026-06-10']);
    VisaApplication::factory()->forTenant($tenant)->stage(VisaStage::Approved)->create(['decision_on' => '2026-06-12']);
    VisaApplication::factory()->forTenant($tenant)->stage(VisaStage::DocumentsPending)->create();

    Booking::factory()->forTenant($tenant)->type(BookingType::AirTicket)->status(BookingStatus::Ticketed)
        ->create(['issued_on' => '2026-06-05', 'depart_on' => '2026-06-18', 'sell_amount' => '90000', 'cost_amount' => '82000', 'commission_amount' => '2500', 'refund_amount' => '0']);
    Booking::factory()->forTenant($tenant)->type(BookingType::Umrah)->status(BookingStatus::Confirmed)
        ->create(['issued_on' => '2026-06-06', 'depart_on' => '2026-08-01', 'sell_amount' => '245000', 'cost_amount' => '210000', 'commission_amount' => '0', 'refund_amount' => '0']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/travel/overview')
        ->assertOk()
        ->assertJsonPath('data.visas.by_stage.submitted', 1)
        ->assertJsonPath('data.visas.in_progress', 2)
        ->assertJsonPath('data.visas.submitted_this_month', 1)
        ->assertJsonPath('data.visas.approved_this_month', 1)
        ->assertJsonPath('data.bookings.by_status.ticketed', 1)
        ->assertJsonPath('data.bookings.by_type.umrah', 1)
        ->assertJsonPath('data.bookings.departing_next_7_days', 1)
        ->assertJsonPath('data.earnings.all_time.net_profit', '45500.00');
});

it('forbids staff from the travel dashboard', function (): void {
    $tenant = makeIndustryTenant('travel');
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/travel/overview')->assertForbidden();
});

it('excludes another tenant\'s rows from the overview', function (): void {
    $tenantA = makeIndustryTenant('travel', ['slug' => 'trep-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeIndustryTenant('travel', ['slug' => 'trep-b']);
    Booking::factory()->forTenant($tenantB)->status(BookingStatus::Ticketed)->create(['sell_amount' => '5000']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/travel/overview')
        ->assertOk()->assertJsonPath('data.earnings.all_time.sell', '0.00');
});
