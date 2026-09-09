<?php

declare(strict_types=1);

use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Models\Lead;

it('logs lead creation and stage changes with a from/to', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum');

    $leadId = $this->postJson('/api/v1/leads', ['name' => 'Tracked'])->assertCreated()->json('data.id');
    $this->putJson("/api/v1/leads/{$leadId}/stage", ['stage' => 'contacted'])->assertOk();

    $events = $this->getJson("/api/v1/leads/{$leadId}/activities")
        ->assertOk()
        ->json('data.*.event');

    expect($events)->toContain('created', 'stage_changed');

    $change = CrmActivity::withoutGlobalScopes()
        ->where('subject_id', $leadId)->where('event', 'stage_changed')->firstOrFail();
    expect($change->properties)->toMatchArray(['from' => 'new', 'to' => 'contacted'])
        ->and($change->causer_id)->toBe($manager->id);
});

it('records a note against a lead', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/notes", [
        'body' => 'Left a voicemail, will retry Tuesday.',
    ])->assertCreated()
        ->assertJsonPath('data.event', 'note')
        ->assertJsonPath('data.properties.note', 'Left a voicemail, will retry Tuesday.');

    expect(CrmActivity::withoutGlobalScopes()->where('event', 'note')->count())->toBe(1);
});

it('hides a lead\'s activity from an uninvolved staff member', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $outsider = makeUser($tenant, 'staff');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/leads/{$lead->id}/activities")->assertOk();
    $this->actingAs($outsider, 'sanctum')->getJson("/api/v1/leads/{$lead->id}/activities")->assertForbidden();
});

it('validates the note body', function (): void {
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $lead = Lead::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->postJson("/api/v1/leads/{$lead->id}/notes", [])
        ->assertUnprocessable()->assertJsonValidationErrors('body');
});
