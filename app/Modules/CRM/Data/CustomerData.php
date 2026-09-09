<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

use App\Modules\CRM\Models\Customer;

/**
 * Application input for creating or updating a {@see Customer}.
 */
final readonly class CustomerData
{
    public function __construct(
        public ?int $ownerEmployeeId,
        public string $name,
        public string $type,
        public ?string $company,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            ownerEmployeeId: isset($validated['owner_employee_id']) ? (int) $validated['owner_employee_id'] : null,
            name: (string) $validated['name'],
            type: (string) ($validated['type'] ?? 'business'),
            company: isset($validated['company']) ? (string) $validated['company'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            address: isset($validated['address']) ? (string) $validated['address'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'owner_employee_id' => $this->ownerEmployeeId,
            'name' => $this->name,
            'type' => $this->type,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ];
    }
}
