<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\RequirementPurpose;

/**
 * Application input for recording what a lead is looking for.
 */
final readonly class PropertyRequirementData
{
    public function __construct(
        public ?string $budgetMin,
        public ?string $budgetMax,
        public ?string $preferredLocations,
        public ?string $unitType,
        public ?int $bedroomsMin,
        public RequirementPurpose $purpose,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            budgetMin: isset($validated['budget_min']) ? (string) $validated['budget_min'] : null,
            budgetMax: isset($validated['budget_max']) ? (string) $validated['budget_max'] : null,
            preferredLocations: isset($validated['preferred_locations']) ? (string) $validated['preferred_locations'] : null,
            unitType: isset($validated['unit_type']) ? (string) $validated['unit_type'] : null,
            bedroomsMin: isset($validated['bedrooms_min']) ? (int) $validated['bedrooms_min'] : null,
            purpose: RequirementPurpose::from((string) ($validated['purpose'] ?? RequirementPurpose::Buy->value)),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'budget_min' => $this->budgetMin,
            'budget_max' => $this->budgetMax,
            'preferred_locations' => $this->preferredLocations,
            'unit_type' => $this->unitType,
            'bedrooms_min' => $this->bedroomsMin,
            'purpose' => $this->purpose,
            'notes' => $this->notes,
        ];
    }
}
