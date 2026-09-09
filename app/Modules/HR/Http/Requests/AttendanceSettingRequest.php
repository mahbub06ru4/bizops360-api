<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\AttendanceSettingData;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceSettingRequest extends FormRequest
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
            'work_starts_at' => ['required', 'date_format:H:i,H:i:s'],
            'work_ends_at' => ['required', 'date_format:H:i,H:i:s', 'after:work_starts_at'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ];
    }

    public function toData(): AttendanceSettingData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return AttendanceSettingData::fromArray($validated);
    }
}
