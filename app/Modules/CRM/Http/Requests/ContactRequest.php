<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Data\ContactData;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): ContactData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return ContactData::fromArray($validated);
    }
}
