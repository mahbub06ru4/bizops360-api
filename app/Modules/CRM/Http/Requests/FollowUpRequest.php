<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Data\FollowUpData;
use App\Modules\CRM\Domain\FollowUpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FollowUpRequest extends FormRequest
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
            'assigned_employee_id' => [
                'nullable', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'type' => ['sometimes', Rule::in(FollowUpType::values())],
            'due_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): FollowUpData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return FollowUpData::fromArray($validated);
    }
}
