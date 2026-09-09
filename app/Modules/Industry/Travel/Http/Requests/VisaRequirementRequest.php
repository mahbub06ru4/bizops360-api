<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\VisaRequirementData;
use Illuminate\Foundation\Http\FormRequest;

class VisaRequirementRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'collected' => ['sometimes', 'boolean'],
            'collected_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): VisaRequirementData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return VisaRequirementData::fromArray($validated);
    }
}
