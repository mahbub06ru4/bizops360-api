<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Requests;

use App\Modules\Industry\RealEstate\Data\LandShareData;
use Illuminate\Foundation\Http\FormRequest;

class LandShareRequest extends FormRequest
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
            'total_shares' => ['required', 'integer', 'min:1', 'max:1000000'],
            'share_value' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ];
    }

    public function toData(): LandShareData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return LandShareData::fromArray($validated);
    }
}
