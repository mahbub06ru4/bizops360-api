<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

/**
 * Application input for a single visa checklist line.
 */
final readonly class VisaRequirementData
{
    public function __construct(
        public string $name,
        public bool $isMandatory,
        public bool $collected,
        public ?string $collectedOn,
        public ?string $note,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        $collected = (bool) ($validated['collected'] ?? false);

        return new self(
            name: (string) $validated['name'],
            isMandatory: (bool) ($validated['is_mandatory'] ?? true),
            collected: $collected,
            collectedOn: isset($validated['collected_on']) ? (string) $validated['collected_on'] : null,
            note: isset($validated['note']) ? (string) $validated['note'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'is_mandatory' => $this->isMandatory,
            'collected' => $this->collected,
            'collected_on' => $this->collected ? ($this->collectedOn ?? now()->toDateString()) : null,
            'note' => $this->note,
        ];
    }
}
