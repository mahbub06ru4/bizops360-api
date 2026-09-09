<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for adding a login user to the current tenant.
 */
final readonly class CreateUserData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public array $roles,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        /** @var list<string> $roles */
        $roles = array_values($validated['roles'] ?? []);

        return new self(
            name: (string) $validated['name'],
            email: (string) $validated['email'],
            password: (string) $validated['password'],
            roles: $roles,
        );
    }
}
