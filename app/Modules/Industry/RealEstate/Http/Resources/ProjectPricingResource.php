<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\ProjectPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProjectPricing
 */
class ProjectPricingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'land_cost' => $this->land_cost,
            'construction_cost' => $this->construction_cost,
            'consultancy_cost' => $this->consultancy_cost,
            'estimated_total' => $this->estimated_total,
            'currency' => $this->currency,
        ];
    }
}
