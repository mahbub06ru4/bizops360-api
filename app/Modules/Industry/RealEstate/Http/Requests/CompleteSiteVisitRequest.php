<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\CompleteSiteVisitData;
use Illuminate\Foundation\Http\FormRequest;

class CompleteSiteVisitRequest extends FormRequest
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
            'feedback' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toData(): CompleteSiteVisitData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return CompleteSiteVisitData::fromArray($validated);
    }
}
