<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\LeaveRequestData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequestRequest extends FormRequest
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
            'employee_id' => [
                'nullable', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'leave_type_id' => [
                'required', 'integer',
                Rule::exists('leave_types', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(): LeaveRequestData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LeaveRequestData::fromArray($validated);
    }
}
