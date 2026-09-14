<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\PropertyRequirementData;
use App\Modules\Industry\RealEstate\Domain\ProjectType;
use App\Modules\Industry\RealEstate\Domain\RequirementPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyRequirementRequest extends FormRequest
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
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99', 'gte:budget_min'],
            'preferred_locations' => ['nullable', 'string', 'max:2000'],
            'unit_type' => ['nullable', Rule::in(ProjectType::values())],
            'bedrooms_min' => ['nullable', 'integer', 'min:0', 'max:50'],
            'purpose' => ['sometimes', Rule::in(RequirementPurpose::values())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toData(): PropertyRequirementData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return PropertyRequirementData::fromArray($validated);
    }
}
