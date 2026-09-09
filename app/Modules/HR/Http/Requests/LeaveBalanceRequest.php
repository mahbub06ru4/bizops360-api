<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\LeaveBalanceData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveBalanceRequest extends FormRequest
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
                'required', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'leave_type_id' => [
                'required', 'integer',
                Rule::exists('leave_types', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'entitled_days' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function toData(): LeaveBalanceData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LeaveBalanceData::fromArray($validated);
    }
}
