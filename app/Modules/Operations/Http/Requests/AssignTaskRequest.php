<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Operations\Data\TaskAssignmentData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTaskRequest extends FormRequest
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
            'assignee_employee_id' => ['present', 'nullable', 'integer', $scoped('employees')],
            'assignee_team_id' => ['present', 'nullable', 'integer', $scoped('teams')],
        ];
    }

    public function toData(): TaskAssignmentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TaskAssignmentData::fromArray($validated);
    }
}
