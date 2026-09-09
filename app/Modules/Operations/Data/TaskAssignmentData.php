<?php

declare(strict_types=1);

namespace App\Modules\Operations\Data;

/**
 * Application input for assigning a task to an employee and/or a team. A null
 * value clears that assignment.
 */
final readonly class TaskAssignmentData
{
    public function __construct(
        public ?int $employeeId,
        public ?int $teamId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: isset($validated['assignee_employee_id']) ? (int) $validated['assignee_employee_id'] : null,
            teamId: isset($validated['assignee_team_id']) ? (int) $validated['assignee_team_id'] : null,
        );
    }
}
