<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\ExpenseData;
use App\Modules\Finance\Domain\ExpenseCategory;
use App\Modules\Finance\Domain\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
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
            'category' => ['sometimes', Rule::in(ExpenseCategory::values())],
            'employee_id' => [
                'nullable', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'spent_on' => ['required', 'date'],
            'method' => ['sometimes', Rule::in(PaymentMethod::values())],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): ExpenseData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ExpenseData::fromArray($validated);
    }
}
