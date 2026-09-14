<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\UnitPriceData;
use App\Modules\Industry\RealEstate\Domain\UnitPriceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitPriceRequest extends FormRequest
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
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'price_type' => ['sometimes', Rule::in(UnitPriceType::values())],
            'effective_from' => ['nullable', 'date'],
        ];
    }

    public function toData(): UnitPriceData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return UnitPriceData::fromArray($validated);
    }
}
