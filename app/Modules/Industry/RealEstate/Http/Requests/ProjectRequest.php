<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\ProjectData;
use App\Modules\Industry\RealEstate\Domain\ProjectType;
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'project_type' => ['sometimes', Rule::in(ProjectType::values())],
            'description' => ['nullable', 'string', 'max:5000'],
            'total_land_area' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }

    public function toData(): ProjectData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ProjectData::fromArray($validated);
    }
}
