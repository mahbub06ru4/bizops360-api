<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

use App\Modules\Organization\Models\Designation;

/**
 * Application input for creating or updating a {@see Designation}.
 */
final readonly class DesignationData
{
    public function __construct(
        public ?int $departmentId,
        public string $title,
        public ?int $rank,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            departmentId: isset($validated['department_id']) ? (int) $validated['department_id'] : null,
            title: (string) $validated['title'],
            rank: isset($validated['rank']) ? (int) $validated['rank'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'department_id' => $this->departmentId,
            'title' => $this->title,
            'rank' => $this->rank,
        ];
    }
}
