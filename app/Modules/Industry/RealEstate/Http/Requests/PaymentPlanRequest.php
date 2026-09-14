<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\PaymentPlanData;
use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentPlanRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'down_payment_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:360'],
            'installment_frequency' => ['sometimes', Rule::in(PaymentPlanFrequency::values())],
        ];
    }

    public function toData(): PaymentPlanData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return PaymentPlanData::fromArray($validated);
    }
}
