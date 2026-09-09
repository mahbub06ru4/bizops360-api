<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for updating a tenant user's profile.
 */
final readonly class UpdateUserData
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            email: (string) $validated['email'],
        );
    }
}
