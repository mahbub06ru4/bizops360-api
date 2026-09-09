<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\RecordVisaDecisionData;
use App\Modules\Industry\Travel\Domain\VisaStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordVisaDecisionRequest extends FormRequest
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
            'outcome' => ['required', Rule::in([VisaStage::Approved->value, VisaStage::Rejected->value])],
            'decision_on' => ['required', 'date'],
            'decision_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): RecordVisaDecisionData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return RecordVisaDecisionData::fromArray($validated);
    }
}
