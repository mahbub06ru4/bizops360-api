<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Resources;

use App\Modules\Industry\Travel\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VisaApplication
 */
class VisaApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'traveller_id' => $this->traveller_id,
            'customer_id' => $this->customer_id,
            'assigned_employee_id' => $this->assigned_employee_id,
            'destination_country' => $this->destination_country,
            'visa_type' => $this->visa_type,
            'mission' => $this->mission,
            'stage' => $this->stage->value,
            'reference_no' => $this->reference_no,
            'application_no' => $this->application_no,
            'government_fee' => $this->government_fee,
            'service_charge' => $this->service_charge,
            'submitted_on' => $this->submitted_on?->toDateString(),
            'decision_on' => $this->decision_on?->toDateString(),
            'decision_note' => $this->decision_note,
            'expected_travel_date' => $this->expected_travel_date?->toDateString(),
            'created_by' => $this->created_by,
            'traveller' => new TravellerResource($this->whenLoaded('traveller')),
            'requirements' => VisaRequirementResource::collection($this->whenLoaded('requirements')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
