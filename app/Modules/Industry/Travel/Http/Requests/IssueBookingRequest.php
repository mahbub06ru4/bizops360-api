<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueBookingRequest extends FormRequest
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
            'pnr' => ['nullable', 'string', 'max:20'],
            'issued_on' => ['nullable', 'date'],
        ];
    }
}
