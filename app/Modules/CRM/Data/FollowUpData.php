<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

use App\Modules\CRM\Domain\FollowUpType;

/**
 * Application input for scheduling or updating a follow-up.
 */
final readonly class FollowUpData
{
    public function __construct(
        public ?int $assignedEmployeeId,
        public FollowUpType $type,
        public string $dueAt,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            assignedEmployeeId: isset($validated['assigned_employee_id']) ? (int) $validated['assigned_employee_id'] : null,
            type: FollowUpType::from((string) ($validated['type'] ?? FollowUpType::Call->value)),
            dueAt: (string) $validated['due_at'],
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'assigned_employee_id' => $this->assignedEmployeeId,
            'type' => $this->type,
            'due_at' => $this->dueAt,
            'notes' => $this->notes,
        ];
    }
}
