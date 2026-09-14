<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for a project's location record.
 */
final readonly class ProjectLocationData
{
    public function __construct(
        public ?string $division,
        public ?string $district,
        public ?string $area,
        public ?string $sector,
        public ?string $road,
        public ?string $landmark,
        public ?string $latitude,
        public ?string $longitude,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            division: isset($validated['division']) ? (string) $validated['division'] : null,
            district: isset($validated['district']) ? (string) $validated['district'] : null,
            area: isset($validated['area']) ? (string) $validated['area'] : null,
            sector: isset($validated['sector']) ? (string) $validated['sector'] : null,
            road: isset($validated['road']) ? (string) $validated['road'] : null,
            landmark: isset($validated['landmark']) ? (string) $validated['landmark'] : null,
            latitude: isset($validated['latitude']) ? (string) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (string) $validated['longitude'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'division' => $this->division,
            'district' => $this->district,
            'area' => $this->area,
            'sector' => $this->sector,
            'road' => $this->road,
            'landmark' => $this->landmark,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
