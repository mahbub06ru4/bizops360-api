<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RaiseBookingInvoiceRequest extends FormRequest
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
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ];
    }
}
