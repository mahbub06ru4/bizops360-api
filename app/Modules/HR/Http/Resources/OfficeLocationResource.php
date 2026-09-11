<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Resources;

use App\Modules\HR\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttendanceSetting
 */
class OfficeLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => $this->label,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'radius_meters' => (int) $this->radius_meters,
            'start_time' => substr((string) $this->work_starts_at, 0, 5),
            'end_time' => substr((string) $this->work_ends_at, 0, 5),
        ];
    }
}
