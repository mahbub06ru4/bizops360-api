<?php

declare(strict_types=1);

namespace App\Modules\Operations\Data;

use App\Modules\Operations\Domain\TaskStatus;

/**
 * Application input for changing a task's status.
 */
final readonly class TaskStatusData
{
    public function __construct(public TaskStatus $status) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            status: TaskStatus::from((string) $validated['status']),
        );
    }
}
