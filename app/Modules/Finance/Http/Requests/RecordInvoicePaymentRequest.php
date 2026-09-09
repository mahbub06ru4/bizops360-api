<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\InvoicePaymentData;
use App\Modules\Finance\Domain\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordInvoicePaymentRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'paid_on' => ['required', 'date'],
            'method' => ['sometimes', Rule::in(PaymentMethod::values())],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): InvoicePaymentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return InvoicePaymentData::fromArray($validated);
    }
}
