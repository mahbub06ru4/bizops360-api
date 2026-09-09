<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Requests;

use App\Modules\Tenant\Data\CompanyProfileData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'legal_name' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', Rule::in(['travel', 'real_estate', 'consultancy'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
        ];
    }

    public function toData(): CompanyProfileData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return CompanyProfileData::fromArray($validated);
    }
}
