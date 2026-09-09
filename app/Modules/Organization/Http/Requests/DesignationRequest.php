<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Data\DesignationData;
use App\Modules\Organization\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignationRequest extends FormRequest
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
        $designation = $this->route('designation');
        $tenantId = $this->user()?->tenant_id;

        return [
            'department_id' => [
                'nullable', 'integer',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'title' => [
                'required', 'string', 'max:255',
                Rule::unique('designations', 'title')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($designation instanceof Designation ? $designation->getKey() : null),
            ],
            'rank' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function toData(): DesignationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return DesignationData::fromArray($validated);
    }
}
