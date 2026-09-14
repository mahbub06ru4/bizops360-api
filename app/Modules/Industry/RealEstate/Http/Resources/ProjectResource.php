<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\ProjectDocument;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The buyer-facing (and seller-facing) shape of a project. Deliberately never
 * includes {@see LandRecord} (legal/
 * private) or {@see ProjectDocument}
 * (private files) — those are served, Policy-gated, through their own
 * dedicated endpoints only.
 *
 * @mixin RealEstateProject
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'project_type' => $this->project_type->value,
            'description' => $this->description,
            'status' => $this->status->value,
            'total_land_area' => $this->total_land_area,
            'currency' => $this->currency,
            'locations' => ProjectLocationResource::collection($this->whenLoaded('locations')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'pricing' => new ProjectPricingResource($this->whenLoaded('pricing')),
            'payment_plans' => ProjectPaymentPlanResource::collection($this->whenLoaded('paymentPlans')),
            'buildings' => BuildingResource::collection($this->whenLoaded('buildings')),
            'land_shares' => LandShareResource::collection($this->whenLoaded('landShares')),
            'verification_reviews' => VerificationReviewResource::collection($this->whenLoaded('verificationReviews')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
