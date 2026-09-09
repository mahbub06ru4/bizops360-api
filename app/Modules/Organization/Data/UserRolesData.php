<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for replacing the role set assigned to a tenant user.
 */
final readonly class UserRolesData
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(public array $roles) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        /** @var list<string> $roles */
        $roles = array_values($validated['roles'] ?? []);

        return new self($roles);
    }
}
