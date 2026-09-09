<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Organization\Data\TeamData;
use App\Modules\Organization\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamRequest extends FormRequest
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
        $team = $this->route('team');
        $tenantId = $this->user()?->tenant_id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('teams', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($team instanceof Team ? $team->getKey() : null),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'lead_employee_id' => [
                'nullable', 'integer',
                Rule::exists('employees', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ];
    }

    public function toData(): TeamData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TeamData::fromArray($validated);
    }
}
