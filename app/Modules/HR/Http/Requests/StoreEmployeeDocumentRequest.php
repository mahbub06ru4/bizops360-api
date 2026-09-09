<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\StoreEmployeeDocumentData;
use App\Modules\HR\Domain\EmployeeDocumentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeDocumentRequest extends FormRequest
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

        return [
            'employee_id' => [
                'required', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'category' => ['required', Rule::in(EmployeeDocumentCategory::values())],
            'title' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function toData(): StoreEmployeeDocumentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return StoreEmployeeDocumentData::fromArray($validated);
    }
}
