<?php

declare(strict_types=1);

namespace App\Modules\Operations\Data;

use App\Modules\Operations\Domain\TaskPriority;
use App\Modules\Operations\Models\Task;

/**
 * Application input for creating or updating a {@see Task}.
 */
final readonly class TaskData
{
    public function __construct(
        public ?int $projectId,
        public ?int $parentTaskId,
        public string $title,
        public ?string $description,
        public TaskPriority $priority,
        public ?string $dueAt,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            projectId: isset($validated['project_id']) ? (int) $validated['project_id'] : null,
            parentTaskId: isset($validated['parent_task_id']) ? (int) $validated['parent_task_id'] : null,
            title: (string) $validated['title'],
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            priority: TaskPriority::from((string) ($validated['priority'] ?? TaskPriority::Normal->value)),
            dueAt: isset($validated['due_at']) ? (string) $validated['due_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'project_id' => $this->projectId,
            'parent_task_id' => $this->parentTaskId,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_at' => $this->dueAt,
        ];
    }
}
