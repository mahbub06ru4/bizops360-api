<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

use App\Modules\HR\Models\LeaveType;

/**
 * Application input for creating or updating a {@see LeaveType}.
 */
final readonly class LeaveTypeData
{
    public function __construct(
        public string $name,
        public string $code,
        public int $defaultDaysPerYear,
        public bool $isPaid,
        public bool $requiresApproval,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            code: (string) $validated['code'],
            defaultDaysPerYear: (int) ($validated['default_days_per_year'] ?? 0),
            isPaid: (bool) ($validated['is_paid'] ?? true),
            requiresApproval: (bool) ($validated['requires_approval'] ?? true),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'default_days_per_year' => $this->defaultDaysPerYear,
            'is_paid' => $this->isPaid,
            'requires_approval' => $this->requiresApproval,
        ];
    }
}
