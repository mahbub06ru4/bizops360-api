<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\LeaveDecisionData;
use Illuminate\Foundation\Http\FormRequest;

class LeaveDecisionRequest extends FormRequest
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

    public function toData(): LeaveDecisionData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LeaveDecisionData::fromArray($validated);
    }
}
