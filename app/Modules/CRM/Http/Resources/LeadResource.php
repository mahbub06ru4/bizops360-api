<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'stage' => $this->stage->value,
            'estimated_value' => $this->estimated_value,
            'notes' => $this->notes,
            'owner_employee_id' => $this->owner_employee_id,
            'converted_customer_id' => $this->converted_customer_id,
            'converted_at' => $this->converted_at?->toIso8601String(),
            'lost_reason' => $this->lost_reason,
            'created_by' => $this->created_by,
            'owner' => new EmployeeResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
