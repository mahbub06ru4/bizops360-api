<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

use App\Modules\CRM\Models\Contact;

/**
 * Application input for creating or updating a {@see Contact}.
 */
final readonly class ContactData
{
    public function __construct(
        public string $name,
        public ?string $title,
        public ?string $email,
        public ?string $phone,
        public bool $isPrimary,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            title: isset($validated['title']) ? (string) $validated['title'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            isPrimary: (bool) ($validated['is_primary'] ?? false),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_primary' => $this->isPrimary,
            'notes' => $this->notes,
        ];
    }
}
