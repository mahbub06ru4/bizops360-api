<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
        $department = $this->route('department');
        $tenantId = $this->user()?->tenant_id;

        return [
            'branch_id' => [
                'nullable', 'integer',
                Rule::exists('branches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('departments', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($department instanceof Department ? $department->getKey() : null),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): DepartmentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return DepartmentData::fromArray($validated);
    }
}
