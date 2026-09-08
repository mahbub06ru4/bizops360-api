<?php

declare(strict_types=1);

namespace App\Modules\Identity\Data;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            email: (string) $validated['email'],
            password: (string) $validated['password'],
            deviceName: (string) ($validated['device_name'] ?? 'api'),
        );
    }
}
