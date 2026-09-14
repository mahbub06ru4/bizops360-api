<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\AmenityData;
use Illuminate\Foundation\Http\FormRequest;

class AmenityRequest extends FormRequest
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
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toData(): AmenityData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return AmenityData::fromArray($validated);
    }
}
