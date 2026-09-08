<?php

declare(strict_types=1);

namespace App\Modules\Identity\Data;

use App\Modules\Identity\Http\Requests\RegisterTenantRequest;

/**
 * Application input for registering a new tenant together with its first (owner)
 * user. Built from {@see RegisterTenantRequest}.
 */
final readonly class RegisterTenantData
{
    public function __construct(
        public string $companyName,
        public ?string $industry,
        public string $ownerName,
        public string $ownerEmail,
        public string $ownerPassword,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            companyName: (string) $validated['company_name'],
            industry: isset($validated['industry']) ? (string) $validated['industry'] : null,
            ownerName: (string) $validated['owner_name'],
            ownerEmail: (string) $validated['owner_email'],
            ownerPassword: (string) $validated['owner_password'],
        );
    }
}
