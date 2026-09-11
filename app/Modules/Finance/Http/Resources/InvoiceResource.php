<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\CRM\Http\Resources\CustomerResource;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Industry\Travel\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'status' => $this->status->value,
            'issue_date' => $this->issue_date->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'amount' => $this->amount,
            'amount_paid' => $this->amount_paid,
            'amount_refunded' => $this->amount_refunded,
            'amount_due' => $this->amountDue(),
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'booking' => $this->whenLoaded('booking', function (): array {
                /** @var Booking $booking */
                $booking = $this->getRelation('booking');

                return [
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'pnr' => $booking->pnr,
                ];
            }),
            'payments' => InvoicePaymentResource::collection($this->whenLoaded('payments')),
            'refunds' => InvoiceRefundResource::collection($this->whenLoaded('refunds')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
