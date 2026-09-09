<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Resources;

use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->toDateString(),
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
            'status' => $this->status->value,
            'worked_minutes' => $this->worked_minutes,
            'is_late' => $this->is_late,
            'is_early_leave' => $this->is_early_leave,
            'note' => $this->note,
            'recorded_by' => $this->recorded_by,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
