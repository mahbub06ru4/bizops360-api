<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\TravellerData;
use App\Modules\Industry\Travel\Domain\TravellerGender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TravellerRequest extends FormRequest
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
        $tenantId = $this->user()?->tenant_id;

        return [
            'customer_id' => [
                'nullable', 'integer',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['sometimes', Rule::in(TravellerGender::values())],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['sometimes', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'passport_expiry' => ['nullable', 'date'],
            'passport_issue_country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): TravellerData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TravellerData::fromArray($validated);
    }
}
