<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitVisaApplicationRequest extends FormRequest
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
            'submitted_on' => ['nullable', 'date'],
            'application_no' => ['nullable', 'string', 'max:100'],
        ];
    }
}
