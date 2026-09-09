<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\InvoiceRefundData;
use App\Modules\Finance\Domain\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundInvoiceRequest extends FormRequest
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
            'payment_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'refunded_on' => ['required', 'date'],
            'method' => ['sometimes', Rule::in(PaymentMethod::values())],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): InvoiceRefundData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return InvoiceRefundData::fromArray($validated);
    }
}
