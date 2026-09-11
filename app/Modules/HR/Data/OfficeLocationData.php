<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

/**
 * Application input for a tenant's geofenced office location and its
 * attendance-window opening/closing time.
 */
final readonly class OfficeLocationData
{
    public function __construct(
        public string $label,
        public float $latitude,
        public float $longitude,
        public int $radiusMeters,
        public string $startTime,
        public string $endTime,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            label: (string) $validated['label'],
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            radiusMeters: (int) $validated['radius_meters'],
            startTime: (string) $validated['start_time'],
            endTime: (string) $validated['end_time'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'label' => $this->label,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radiusMeters,
            'work_starts_at' => $this->startTime,
            'work_ends_at' => $this->endTime,
        ];
    }
}
