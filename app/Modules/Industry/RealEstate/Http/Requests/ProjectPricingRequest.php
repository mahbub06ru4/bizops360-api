<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\ProjectPricingData;
use Illuminate\Foundation\Http\FormRequest;

class ProjectPricingRequest extends FormRequest
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
            'land_cost' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'construction_cost' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'consultancy_cost' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }

    public function toData(): ProjectPricingData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ProjectPricingData::fromArray($validated);
    }
}
