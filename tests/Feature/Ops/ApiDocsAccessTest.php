<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('allows viewing the API docs, guest or authenticated', function (): void {
    // Regression: Gate::allows() always passes the (possibly null) user as
    // the first argument to the ability closure — a zero-arg closure here
    // silently evaluates to false instead of true. See AppServiceProvider.
    expect(Gate::allows('viewApiDocs'))->toBeTrue();

    $tenant = makeTenant();
    $user = makeUser($tenant);
    clearTenantContext();

    $this->actingAs($user, 'sanctum');
    expect(Gate::allows('viewApiDocs'))->toBeTrue();
});

it('serves the OpenAPI document at /docs/api.json', function (): void {
    $this->get('/docs/api.json')
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0');
});
