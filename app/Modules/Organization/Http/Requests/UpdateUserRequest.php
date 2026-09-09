<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Requests;

use App\Models\User;
use App\Modules\Organization\Data\UpdateUserData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user instanceof User ? $user->getKey() : null),
            ],
        ];
    }

    public function toData(): UpdateUserData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return UpdateUserData::fromArray($validated);
    }
}
