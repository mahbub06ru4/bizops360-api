<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Data\TeamMembersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamMembersRequest extends FormRequest
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
            'members' => ['present', 'array'],
            'members.*' => [
                'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ];
    }

    public function toData(): TeamMembersData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TeamMembersData::fromArray($validated);
    }
}
