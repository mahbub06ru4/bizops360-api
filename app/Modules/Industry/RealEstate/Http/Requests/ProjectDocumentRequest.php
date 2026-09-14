<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\ProjectDocumentData;
use App\Modules\Industry\RealEstate\Domain\ProjectDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectDocumentRequest extends FormRequest
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
        return [
            'document_type' => ['sometimes', Rule::in(ProjectDocumentType::values())],
            'is_private' => ['sometimes', 'boolean'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function toData(): ProjectDocumentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ProjectDocumentData::fromArray($validated);
    }
}
