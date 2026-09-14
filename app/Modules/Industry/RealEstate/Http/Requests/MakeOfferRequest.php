<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\MakeOfferData;
use App\Modules\Industry\RealEstate\Domain\OfferedBy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MakeOfferRequest extends FormRequest
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
            'unit_id' => ['required', 'integer'],
            'offered_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'offered_by' => ['sometimes', Rule::in(OfferedBy::values())],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toData(): MakeOfferData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return MakeOfferData::fromArray($validated);
    }
}
