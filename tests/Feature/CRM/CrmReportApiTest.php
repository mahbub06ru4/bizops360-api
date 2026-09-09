<?php

declare(strict_types=1);

use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('returns the tenant CRM overview', function (): void {
    Carbon::setTestNow('2026-06-15 09:00:00');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');

    Lead::factory()->forTenant($tenant)->stage(LeadStage::New)->create(['estimated_value' => 1000]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Negotiation)->create(['estimated_value' => 4000]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Converted)->create(['estimated_value' => 9000]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Converted)->create(['estimated_value' => 0]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Lost)->create(['estimated_value' => 0]);
    Customer::factory()->forTenant($tenant)->create();

    $lead = Lead::factory()->forTenant($tenant)->stage(LeadStage::New)->create(['estimated_value' => 0]);
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create(['due_at' => '2026-06-15 14:00:00']);
    FollowUp::factory()->forTenant($tenant)->attachedTo($lead)->create(['due_at' => '2026-06-14 09:00:00']);
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/crm/overview')
        ->assertOk()
        ->assertJsonPath('data.leads.open', 3)
        ->assertJsonPath('data.leads.converted', 2)
        ->assertJsonPath('data.leads.lost', 1)
        ->assertJsonPath('data.leads.conversion_rate', 66.7)
        ->assertJsonPath('data.leads.open_pipeline_value', '5000.00')
        ->assertJsonPath('data.leads.by_stage.negotiation', 1)
        ->assertJsonPath('data.customers', 1)
        ->assertJsonPath('data.follow_ups.due_today', 1)
        ->assertJsonPath('data.follow_ups.overdue', 1);
});

it('reports sales performance per owner employee', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $emp = Employee::factory()->forTenant($tenant)->create(['first_name' => 'Sam', 'last_name' => 'Seller']);

    Lead::factory()->forTenant($tenant)->stage(LeadStage::Converted)->create(['owner_employee_id' => $emp->id, 'estimated_value' => 8000]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::Lost)->create(['owner_employee_id' => $emp->id, 'estimated_value' => 0]);
    Lead::factory()->forTenant($tenant)->stage(LeadStage::New)->create(['owner_employee_id' => $emp->id, 'estimated_value' => 0]);
    Customer::factory()->forTenant($tenant)->create(['owner_employee_id' => $emp->id]);
    clearTenantContext();

    $data = $this->actingAs($manager, 'sanctum')->getJson('/api/v1/crm/sales-performance')
        ->assertOk()->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['employee_id'])->toBe($emp->id)
        ->and($data[0]['employee_name'])->toBe('Sam Seller')
        ->and($data[0]['converted_leads'])->toBe(1)
        ->and($data[0]['lost_leads'])->toBe(1)
        ->and($data[0]['open_leads'])->toBe(1)
        ->and($data[0]['win_rate'])->toBe(50.0)
        ->and($data[0]['converted_value'])->toBe('8000.00')
        ->and($data[0]['customers'])->toBe(1);
});

it('forbids staff from the CRM dashboards', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/crm/overview')->assertForbidden();
    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/crm/sales-performance')->assertForbidden();
});
