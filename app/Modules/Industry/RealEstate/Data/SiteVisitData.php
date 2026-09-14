<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for scheduling a site visit.
 */
final readonly class SiteVisitData
{
    public function __construct(
        public ?int $unitId,
        public ?int $projectId,
        public string $scheduledAt,
        public ?int $conductedByEmployeeId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            unitId: isset($validated['unit_id']) ? (int) $validated['unit_id'] : null,
            projectId: isset($validated['project_id']) ? (int) $validated['project_id'] : null,
            scheduledAt: (string) $validated['scheduled_at'],
            conductedByEmployeeId: isset($validated['conducted_by_employee_id']) ? (int) $validated['conducted_by_employee_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'unit_id' => $this->unitId,
            'project_id' => $this->projectId,
            'scheduled_at' => $this->scheduledAt,
            'conducted_by_employee_id' => $this->conductedByEmployeeId,
        ];
    }
}
