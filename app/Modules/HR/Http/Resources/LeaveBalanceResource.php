<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Resources;

use App\Modules\HR\Models\LeaveBalance;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LeaveBalance
 */
class LeaveBalanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $this->year,
            'entitled_days' => $this->entitled_days,
            'used_days' => $this->used_days,
            'remaining_days' => (int) $this->entitled_days - (int) $this->used_days,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
        ];
    }
}
