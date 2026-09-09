<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Modules\Authorization\Roles;
use App\Modules\Organization\Data\UserRolesData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRolesRequest extends FormRequest
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
            'roles' => ['present', 'array'],
            'roles.*' => [Rule::in(Roles::all())],
        ];
    }

    public function toData(): UserRolesData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return UserRolesData::fromArray($validated);
    }
}
