<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

use App\Modules\CRM\Models\Lead;

/**
 * Application input for creating or updating a {@see Lead}.
 */
final readonly class LeadData
{
    public function __construct(
        public ?int $ownerEmployeeId,
        public string $name,
        public ?string $company,
        public ?string $email,
        public ?string $phone,
        public ?string $source,
        public ?string $estimatedValue,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            ownerEmployeeId: isset($validated['owner_employee_id']) ? (int) $validated['owner_employee_id'] : null,
            name: (string) $validated['name'],
            company: isset($validated['company']) ? (string) $validated['company'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            source: isset($validated['source']) ? (string) $validated['source'] : null,
            estimatedValue: isset($validated['estimated_value']) ? (string) $validated['estimated_value'] : null,
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
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
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'estimated_value' => $this->estimatedValue,
            'notes' => $this->notes,
        ];
    }
}
