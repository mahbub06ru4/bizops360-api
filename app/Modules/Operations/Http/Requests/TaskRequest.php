<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Operations\Data\TaskData;
use App\Modules\Operations\Domain\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        $scoped = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        return [
            'project_id' => ['nullable', 'integer', $scoped('projects')],
            'parent_task_id' => ['nullable', 'integer', $scoped('tasks')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', Rule::in(TaskPriority::values())],
            'due_at' => ['nullable', 'date'],
        ];
    }

    public function toData(): TaskData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TaskData::fromArray($validated);
    }
}
