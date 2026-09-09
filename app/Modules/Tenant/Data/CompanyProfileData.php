<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Data;

/**
 * Application input for updating the current tenant's company profile.
 */
final readonly class CompanyProfileData
{
    public function __construct(
        public string $name,
        public ?string $legalName,
        public ?string $industry,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public string $timezone,
        public string $currency,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            legalName: isset($validated['legal_name']) ? (string) $validated['legal_name'] : null,
            industry: isset($validated['industry']) ? (string) $validated['industry'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            address: isset($validated['address']) ? (string) $validated['address'] : null,
            timezone: (string) ($validated['timezone'] ?? 'UTC'),
            currency: (string) ($validated['currency'] ?? 'USD'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'industry' => $this->industry,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
        ];
    }
}
