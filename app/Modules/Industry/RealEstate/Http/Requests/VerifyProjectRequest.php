<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\VerifyProjectData;
use Illuminate\Foundation\Http\FormRequest;

class VerifyProjectRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toData(): VerifyProjectData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return VerifyProjectData::fromArray($validated);
    }
}
