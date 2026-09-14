<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for adding a building to a project.
 */
final readonly class BuildingData
{
    public function __construct(
        public string $name,
        public int $totalFloors,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            totalFloors: (int) ($validated['total_floors'] ?? 1),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'total_floors' => $this->totalFloors,
        ];
    }
}
