<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\UnitMediaData;
use App\Modules\Industry\RealEstate\Domain\UnitMediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitMediaRequest extends FormRequest
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
            'media_type' => ['sometimes', Rule::in(UnitMediaType::values())],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            'file' => ['required', 'file', 'max:51200', 'mimes:jpg,jpeg,png,mp4,mov,pdf'],
        ];
    }

    public function toData(): UnitMediaData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return UnitMediaData::fromArray($validated);
    }
}
