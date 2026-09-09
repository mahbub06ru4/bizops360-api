<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\VisaRequirement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VisaRequirement
 */
class VisaRequirementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visa_application_id' => $this->visa_application_id,
            'name' => $this->name,
            'is_mandatory' => $this->is_mandatory,
            'collected' => $this->collected,
            'collected_on' => $this->collected_on?->toDateString(),
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
