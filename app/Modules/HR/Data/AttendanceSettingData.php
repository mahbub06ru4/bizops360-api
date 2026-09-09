<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

/**
 * Application input for a tenant's working-hours configuration.
 */
final readonly class AttendanceSettingData
{
    public function __construct(
        public string $workStartsAt,
        public string $workEndsAt,
        public int $graceMinutes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            workStartsAt: (string) $validated['work_starts_at'],
            workEndsAt: (string) $validated['work_ends_at'],
            graceMinutes: (int) $validated['grace_minutes'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'work_starts_at' => $this->workStartsAt,
            'work_ends_at' => $this->workEndsAt,
            'grace_minutes' => $this->graceMinutes,
        ];
    }
}
