<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\ProjectPaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProjectPaymentPlan
 */
class ProjectPaymentPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'down_payment_percent' => $this->down_payment_percent,
            'installment_count' => $this->installment_count,
            'installment_frequency' => $this->installment_frequency->value,
        ];
    }
}
