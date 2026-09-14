<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\InstallmentPlanData;
use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstallmentPlanRequest extends FormRequest
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
            'down_payment_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:360'],
            'frequency' => ['required', Rule::in(PaymentPlanFrequency::values())],
            'start_date' => ['required', 'date'],
        ];
    }

    public function toData(): InstallmentPlanData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return InstallmentPlanData::fromArray($validated);
    }
}
