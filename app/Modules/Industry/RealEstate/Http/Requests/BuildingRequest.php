<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\BuildingData;
use Illuminate\Foundation\Http\FormRequest;

class BuildingRequest extends FormRequest
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
            'total_floors' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function toData(): BuildingData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return BuildingData::fromArray($validated);
    }
}
