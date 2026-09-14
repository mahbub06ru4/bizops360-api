<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\ProjectLocationData;
use Illuminate\Foundation\Http\FormRequest;

class ProjectLocationRequest extends FormRequest
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
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:50'],
            'road' => ['nullable', 'string', 'max:100'],
            'landmark' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function toData(): ProjectLocationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ProjectLocationData::fromArray($validated);
    }
}
