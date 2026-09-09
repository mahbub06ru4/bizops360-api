<?php

declare(strict_types=1);

namespace App\Modules\Operations\Data;

use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Operations\Models\Project;

/**
 * Application input for creating or updating a {@see Project}.
 */
final readonly class ProjectData
{
    public function __construct(
        public ?int $departmentId,
        public ?int $leadEmployeeId,
        public string $name,
        public string $code,
        public ?string $description,
        public ProjectStatus $status,
        public ?string $startDate,
        public ?string $dueDate,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            departmentId: isset($validated['department_id']) ? (int) $validated['department_id'] : null,
            leadEmployeeId: isset($validated['lead_employee_id']) ? (int) $validated['lead_employee_id'] : null,
            name: (string) $validated['name'],
            code: (string) $validated['code'],
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            status: ProjectStatus::from((string) ($validated['status'] ?? ProjectStatus::Planning->value)),
            startDate: isset($validated['start_date']) ? (string) $validated['start_date'] : null,
            dueDate: isset($validated['due_date']) ? (string) $validated['due_date'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'department_id' => $this->departmentId,
            'lead_employee_id' => $this->leadEmployeeId,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->startDate,
            'due_date' => $this->dueDate,
        ];
    }
}
