<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\CRM\Http\Resources\CustomerResource;
use App\Modules\Finance\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Income
 */
class IncomeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'category' => $this->category->value,
            'source' => $this->source,
            'amount' => $this->amount,
            'received_on' => $this->received_on->toDateString(),
            'method' => $this->method->value,
            'reference' => $this->reference,
            'note' => $this->note,
            'recorded_by' => $this->recorded_by,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
