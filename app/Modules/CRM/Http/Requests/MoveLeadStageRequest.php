<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Data\LeadStageData;
use App\Modules\CRM\Domain\LeadStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveLeadStageRequest extends FormRequest
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
            'stage' => ['required', Rule::in(LeadStage::values()), Rule::notIn([LeadStage::Converted->value])],
            'lost_reason' => ['nullable', 'string', 'max:500', 'required_if:stage,'.LeadStage::Lost->value],
        ];
    }

    public function toData(): LeadStageData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LeadStageData::fromArray($validated);
    }
}
