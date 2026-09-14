<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\SiteVisitData;
use Illuminate\Foundation\Http\FormRequest;

class SiteVisitRequest extends FormRequest
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
            'unit_id' => ['nullable', 'integer', 'required_without:project_id'],
            'project_id' => ['nullable', 'integer', 'required_without:unit_id'],
            'scheduled_at' => ['required', 'date'],
            'conducted_by_employee_id' => ['nullable', 'integer'],
        ];
    }

    public function toData(): SiteVisitData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return SiteVisitData::fromArray($validated);
    }
}
