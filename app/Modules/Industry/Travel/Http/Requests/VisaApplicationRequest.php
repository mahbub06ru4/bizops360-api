<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\VisaApplicationData;
use App\Modules\Industry\Travel\Data\VisaRequirementData;
use App\Modules\Industry\Travel\Models\VisaApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisaApplicationRequest extends FormRequest
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
        $tenantScoped = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        $rules = [
            'customer_id' => ['nullable', 'integer', $tenantScoped('customers')],
            'assigned_employee_id' => ['nullable', 'integer', $tenantScoped('employees')],
            'destination_country' => ['required', 'string', 'max:100'],
            'visa_type' => ['sometimes', 'string', 'max:50'],
            'mission' => ['nullable', 'string', 'max:150'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'application_no' => ['nullable', 'string', 'max:100'],
            'government_fee' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'service_charge' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'expected_travel_date' => ['nullable', 'date'],
            'requirements' => ['sometimes', 'array'],
            'requirements.*.name' => ['required_with:requirements', 'string', 'max:150'],
            'requirements.*.is_mandatory' => ['sometimes', 'boolean'],
            'requirements.*.collected' => ['sometimes', 'boolean'],
            'requirements.*.collected_on' => ['nullable', 'date'],
            'requirements.*.note' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->isMethod('POST')) {
            $rules['traveller_id'] = ['required', 'integer', $tenantScoped('travellers')];
        }

        return $rules;
    }

    public function toData(): VisaApplicationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        if (! isset($validated['traveller_id'])) {
            /** @var VisaApplication $application */
            $application = $this->route('visaApplication');
            $validated['traveller_id'] = $application->traveller_id;
        }

        return VisaApplicationData::fromArray($validated);
    }

    /**
     * @return list<VisaRequirementData>
     */
    public function requirements(): array
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();
        /** @var array<int, array<string, mixed>> $rows */
        $rows = array_values($validated['requirements'] ?? []);

        $data = [];
        foreach ($rows as $row) {
            $data[] = VisaRequirementData::fromArray($row);
        }

        return $data;
    }
}
