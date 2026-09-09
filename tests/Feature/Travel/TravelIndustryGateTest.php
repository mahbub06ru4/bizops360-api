<?php

declare(strict_types=1);

it('serves the travel module to a travel tenant', function (): void {
    $tenant = makeIndustryTenant('travel');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/travellers')->assertOk();
    $this->actingAs($owner, 'sanctum')->getJson('/api/v1/travel/overview')->assertOk();
});

it('forbids the travel module for a non-travel tenant', function (): void {
    $tenant = makeIndustryTenant('real_estate');
    $owner = makeUser($tenant, 'owner');
    clearTenantContext();

    foreach (['travellers', 'visa-applications', 'bookings', 'travel/overview'] as $path) {
        $this->actingAs($owner, 'sanctum')->getJson("/api/v1/{$path}")->assertForbidden();
    }

    $this->actingAs($owner, 'sanctum')->postJson('/api/v1/travellers', ['full_name' => 'X'])->assertForbidden();
});
