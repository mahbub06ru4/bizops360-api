<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for creating or updating a {@see \App\Modules\Organization\Models\Department}.
 */
final readonly class DepartmentData
{
    public function __construct(
        public ?int $branchId,
        public string $name,
        public string $code,
        public ?string $description,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            name: (string) $validated['name'],
            code: (string) $validated['code'],
            description: isset($validated['description']) ? (string) $validated['description'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'branch_id' => $this->branchId,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
