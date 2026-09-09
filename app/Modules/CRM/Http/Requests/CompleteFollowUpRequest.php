<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Data\FollowUpOutcomeData;
use Illuminate\Foundation\Http\FormRequest;

class CompleteFollowUpRequest extends FormRequest
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
            'outcome' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): FollowUpOutcomeData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return FollowUpOutcomeData::fromArray($validated);
    }
}
