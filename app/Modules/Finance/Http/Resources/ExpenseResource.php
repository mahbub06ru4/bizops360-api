<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\Expense;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'employee_id' => $this->employee_id,
            'supplier_name' => $this->supplier_name,
            'title' => $this->title,
            'amount' => $this->amount,
            'spent_on' => $this->spent_on->toDateString(),
            'method' => $this->method->value,
            'reference' => $this->reference,
            'note' => $this->note,
            'recorded_by' => $this->recorded_by,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
