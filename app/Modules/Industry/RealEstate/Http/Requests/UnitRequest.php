<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\UnitData;
use App\Modules\Industry\RealEstate\Domain\UnitFacing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
            'unit_number' => ['required', 'string', 'max:50'],
            'floor' => ['required', 'integer', 'min:0', 'max:300'],
            'size_sqft' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'facing' => ['nullable', Rule::in(UnitFacing::values())],
            'parking_spaces' => ['sometimes', 'integer', 'min:0', 'max:50'],
        ];
    }

    public function toData(): UnitData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return UnitData::fromArray($validated);
    }
}
