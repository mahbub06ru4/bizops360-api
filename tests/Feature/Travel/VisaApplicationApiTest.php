<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Models\Tenant;

function openVisa(int $tenantId, array $overrides = []): array
{
    $traveller = Traveller::factory()->forTenant(Tenant::find($tenantId))->create();

    $response = test()->postJson('/api/v1/visa-applications', array_merge([
        'traveller_id' => $traveller->id,
        'destination_country' => 'Thailand',
        'visa_type' => 'tourist',
        'requirements' => [
            ['name' => 'Passport', 'is_mandatory' => true],
            ['name' => 'Bank statement', 'is_mandatory' => true],
        ],
    ], $overrides))->assertCreated();

    return [$response->json('data.id'), $traveller->id, $response];
}

it('drives a visa application through the full pipeline', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    [$id] = openVisa($tenant->id);
    $this->getJson("/api/v1/visa-applications/{$id}")->assertJsonPath('data.stage', 'documents_pending');

    // cannot submit while a mandatory doc is missing
    $this->postJson("/api/v1/visa-applications/{$id}/submit")->assertUnprocessable();

    $reqs = $this->getJson("/api/v1/visa-applications/{$id}")->json('data.requirements');
    foreach ($reqs as $req) {
        $this->putJson("/api/v1/visa-requirements/{$req['id']}", ['collected' => true])->assertOk();
    }
    $this->getJson("/api/v1/visa-applications/{$id}")->assertJsonPath('data.stage', 'documents_collected');

    $this->postJson("/api/v1/visa-applications/{$id}/submit", ['application_no' => 'TH-1'])
        ->assertOk()->assertJsonPath('data.stage', 'submitted');
    $this->postJson("/api/v1/visa-applications/{$id}/processing")->assertOk()->assertJsonPath('data.stage', 'processing');
    $this->postJson("/api/v1/visa-applications/{$id}/decision", [
        'outcome' => 'approved', 'decision_on' => '2026-07-01',
    ])->assertOk()->assertJsonPath('data.stage', 'approved');
});

it('rejects an illegal stage jump', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    [$id] = openVisa($tenant->id);

    $this->postJson("/api/v1/visa-applications/{$id}/processing")->assertUnprocessable();
    $this->postJson("/api/v1/visa-applications/{$id}/decision", [
        'outcome' => 'approved', 'decision_on' => '2026-07-01',
    ])->assertUnprocessable();
});

it('can be cancelled from an open stage but not deleted once submitted', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();
    $this->actingAs($manager, 'sanctum');

    [$id] = openVisa($tenant->id);
    $this->postJson("/api/v1/visa-applications/{$id}/cancel", ['reason' => 'Client dropped'])
        ->assertOk()->assertJsonPath('data.stage', 'cancelled');
    $this->deleteJson("/api/v1/visa-applications/{$id}")->assertForbidden();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/visa-applications/{$id}")->assertNoContent();
});

it('scopes visa decisions to the decide permission', function (): void {
    $tenant = makeIndustryTenant('travel');
    $manager = makeUser($tenant, 'manager');
    $staff = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');
    [$id] = openVisa($tenant->id);

    $this->actingAs($staff, 'sanctum')
        ->postJson("/api/v1/visa-applications/{$id}/submit")->assertForbidden();
});

it('404s on another tenant\'s visa application', function (): void {
    $tenantA = makeIndustryTenant('travel', ['slug' => 'visa-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeIndustryTenant('travel', ['slug' => 'visa-iso-b']);
    $foreign = VisaApplication::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/visa-applications/{$foreign->id}")->assertNotFound();
});
