<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Operations\Data\ProjectData;
use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Operations\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $project = $this->route('project');
        $tenantId = $this->user()?->tenant_id;

        $scoped = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        return [
            'department_id' => ['nullable', 'integer', $scoped('departments')],
            'lead_employee_id' => ['nullable', 'integer', $scoped('employees')],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('projects', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($project instanceof Project ? $project->getKey() : null),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in(ProjectStatus::values())],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function toData(): ProjectData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ProjectData::fromArray($validated);
    }
}
