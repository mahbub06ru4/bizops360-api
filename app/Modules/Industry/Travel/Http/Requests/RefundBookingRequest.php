<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\RefundBookingData;
use Illuminate\Foundation\Http\FormRequest;

class RefundBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'refunded_on' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toData(): RefundBookingData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return RefundBookingData::fromArray($validated);
    }
}
