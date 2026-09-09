<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleVisaRequirementRequest extends FormRequest
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
            'collected' => ['required', 'boolean'],
            'collected_on' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
