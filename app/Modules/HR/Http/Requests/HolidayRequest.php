<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\HolidayData;
use App\Modules\HR\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
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
        $holiday = $this->route('holiday');
        $tenantId = $this->user()?->tenant_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => [
                'required', 'date',
                Rule::unique('holidays', 'date')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($holiday instanceof Holiday ? $holiday->getKey() : null),
            ],
            'is_recurring' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): HolidayData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return HolidayData::fromArray($validated);
    }
}
