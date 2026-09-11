<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Requests;

use App\Modules\HR\Data\OfficeLocationData;
use Illuminate\Foundation\Http\FormRequest;

class OfficeLocationRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:100000'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s', 'after:start_time'],
        ];
    }

    public function toData(): OfficeLocationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return OfficeLocationData::fromArray($validated);
    }
}
