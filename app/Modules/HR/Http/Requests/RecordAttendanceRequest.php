<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\RecordAttendanceData;
use App\Modules\HR\Domain\AttendanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordAttendanceRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(AttendanceStatus::values())],
            'check_in_at' => ['nullable', 'date'],
            'check_out_at' => ['nullable', 'date', 'after_or_equal:check_in_at'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toData(): RecordAttendanceData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return RecordAttendanceData::fromArray($validated);
    }
}
