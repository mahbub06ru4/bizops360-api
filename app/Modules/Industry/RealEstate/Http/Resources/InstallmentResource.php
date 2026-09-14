<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Installment
 */
class InstallmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'installment_plan_id' => $this->installment_plan_id,
            'sequence' => $this->sequence,
            'due_date' => $this->due_date->toDateString(),
            'amount' => $this->amount,
            'status' => $this->status->value,
            'invoice_id' => $this->invoice_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
