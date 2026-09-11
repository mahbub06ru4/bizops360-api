<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Requests;

use App\Modules\Finance\Data\ExpenseDecisionData;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseDecisionRequest extends FormRequest
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
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(): ExpenseDecisionData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ExpenseDecisionData::fromArray($validated);
    }
}
