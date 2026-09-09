<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\LeaveTypeData;
use App\Modules\HR\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
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
        $leaveType = $this->route('leaveType');
        $tenantId = $this->user()?->tenant_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('leave_types', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($leaveType instanceof LeaveType ? $leaveType->getKey() : null),
            ],
            'default_days_per_year' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_paid' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): LeaveTypeData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LeaveTypeData::fromArray($validated);
    }
}
