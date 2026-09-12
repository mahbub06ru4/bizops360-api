<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Requests;

use App\Modules\Billing\Data\ChangePlanData;
use Illuminate\Foundation\Http\FormRequest;

class ChangePlanRequest extends FormRequest
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
            'plan_code' => ['required', 'string', 'exists:plans,code'],
        ];
    }

    public function toData(): ChangePlanData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ChangePlanData::fromArray($validated);
    }
}
