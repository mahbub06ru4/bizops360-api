<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

use App\Modules\HR\Models\Holiday;

/**
 * Application input for creating or updating a {@see Holiday}.
 */
final readonly class HolidayData
{
    public function __construct(
        public string $name,
        public string $date,
        public bool $isRecurring,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            date: (string) $validated['date'],
            isRecurring: (bool) ($validated['is_recurring'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'date' => $this->date,
            'is_recurring' => $this->isRecurring,
        ];
    }
}
