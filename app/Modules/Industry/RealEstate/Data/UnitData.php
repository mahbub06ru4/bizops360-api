<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\UnitFacing;

/**
 * Application input for adding a unit to a building.
 */
final readonly class UnitData
{
    public function __construct(
        public string $unitNumber,
        public int $floor,
        public string $sizeSqft,
        public ?int $bedrooms,
        public ?int $bathrooms,
        public ?UnitFacing $facing,
        public int $parkingSpaces,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            unitNumber: (string) $validated['unit_number'],
            floor: (int) $validated['floor'],
            sizeSqft: (string) $validated['size_sqft'],
            bedrooms: isset($validated['bedrooms']) ? (int) $validated['bedrooms'] : null,
            bathrooms: isset($validated['bathrooms']) ? (int) $validated['bathrooms'] : null,
            facing: isset($validated['facing']) ? UnitFacing::from((string) $validated['facing']) : null,
            parkingSpaces: (int) ($validated['parking_spaces'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'unit_number' => $this->unitNumber,
            'floor' => $this->floor,
            'size_sqft' => $this->sizeSqft,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'facing' => $this->facing,
            'parking_spaces' => $this->parkingSpaces,
        ];
    }
}
