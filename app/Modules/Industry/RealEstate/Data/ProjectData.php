<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\ProjectType;

/**
 * Application input for creating or updating a RealEstateProject.
 */
final readonly class ProjectData
{
    public function __construct(
        public string $name,
        public ProjectType $projectType,
        public ?string $description,
        public ?string $totalLandArea,
        public string $currency,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            projectType: ProjectType::from((string) ($validated['project_type'] ?? ProjectType::Apartment->value)),
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            totalLandArea: isset($validated['total_land_area']) ? (string) $validated['total_land_area'] : null,
            currency: strtoupper((string) ($validated['currency'] ?? 'BDT')),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'project_type' => $this->projectType,
            'description' => $this->description,
            'total_land_area' => $this->totalLandArea,
            'currency' => $this->currency,
        ];
    }
}
