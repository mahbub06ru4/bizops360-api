<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\IncomeData;
use App\Modules\Finance\Domain\IncomeCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'category' => ['sometimes', Rule::in(IncomeCategory::values())],
            'source' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'received_on' => ['required', 'date'],
            'method' => ['sometimes', Rule::in(PaymentMethod::values())],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): IncomeData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return IncomeData::fromArray($validated);
    }
}
