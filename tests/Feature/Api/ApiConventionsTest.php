<?php

declare(strict_types=1);
use Dedoc\Scramble\Generator;

it('returns a JSON 401 for an unauthenticated API request even without an Accept header', function (): void {
    $response = $this->get('/api/v1/auth/me');

    $response->assertUnauthorized()
        ->assertHeader('content-type', 'application/json')
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('generates the OpenAPI document from code', function (): void {
    $document = app(Generator::class)();

    expect($document['openapi'])->toStartWith('3.')
        ->and($document['paths'])->toHaveKeys([
            '/auth/register',
            '/auth/login',
            '/auth/me',
        ]);
});

it('throttles repeated login attempts', function (): void {
    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'x@y.test', 'password' => 'nope']);
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'x@y.test', 'password' => 'nope'])
        ->assertStatus(429);
});
