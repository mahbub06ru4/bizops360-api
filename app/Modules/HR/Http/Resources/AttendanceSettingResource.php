<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Resources;

use App\Modules\HR\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttendanceSetting
 */
class AttendanceSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'work_starts_at' => substr((string) $this->work_starts_at, 0, 5),
            'work_ends_at' => substr((string) $this->work_ends_at, 0, 5),
            'grace_minutes' => (int) $this->grace_minutes,
        ];
    }
}
