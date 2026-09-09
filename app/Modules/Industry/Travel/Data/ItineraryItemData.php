<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

/**
 * Application input for one day of a package itinerary.
 */
final readonly class ItineraryItemData
{
    public function __construct(
        public int $dayNumber,
        public string $title,
        public ?string $description,
        public ?string $city,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row, int $fallbackDay): self
    {
        return new self(
            dayNumber: isset($row['day_number']) ? (int) $row['day_number'] : $fallbackDay,
            title: (string) $row['title'],
            description: isset($row['description']) ? (string) $row['description'] : null,
            city: isset($row['city']) ? (string) $row['city'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'day_number' => $this->dayNumber,
            'title' => $this->title,
            'description' => $this->description,
            'city' => $this->city,
        ];
    }
}
