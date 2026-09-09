<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for creating or updating a {@see \App\Modules\Organization\Models\Branch}.
 */
final readonly class BranchData
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public bool $isHeadOffice,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            code: (string) $validated['code'],
            address: isset($validated['address']) ? (string) $validated['address'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            isHeadOffice: (bool) ($validated['is_head_office'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_head_office' => $this->isHeadOffice,
        ];
    }
}
