<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\LandRecordData;
use Illuminate\Foundation\Http\FormRequest;

class LandRecordRequest extends FormRequest
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
            'mouza' => ['nullable', 'string', 'max:150'],
            'jl_no' => ['nullable', 'string', 'max:50'],
            'khatian_no' => ['nullable', 'string', 'max:50'],
            'dag_no' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function toData(): LandRecordData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LandRecordData::fromArray($validated);
    }
}
