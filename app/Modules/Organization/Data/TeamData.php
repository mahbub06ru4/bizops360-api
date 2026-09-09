<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

use App\Modules\Organization\Models\Team;

/**
 * Application input for creating or updating a {@see Team}.
 */
final readonly class TeamData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?int $leadEmployeeId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            leadEmployeeId: isset($validated['lead_employee_id']) ? (int) $validated['lead_employee_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'lead_employee_id' => $this->leadEmployeeId,
        ];
    }
}
