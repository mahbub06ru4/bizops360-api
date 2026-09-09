<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\InvoiceData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
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
        $tenantId = $this->user()?->tenant_id;

        return [
            'customer_id' => [
                'required', 'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toData(): InvoiceData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return InvoiceData::fromArray($validated);
    }
}
