<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for adding an amenity to a project.
 */
final readonly class AmenityData
{
    public function __construct(
        public string $name,
        public ?string $icon,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            icon: isset($validated['icon']) ? (string) $validated['icon'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'icon' => $this->icon,
        ];
    }
}
