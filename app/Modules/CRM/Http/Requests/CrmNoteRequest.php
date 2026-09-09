<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Data\CrmNoteData;
use Illuminate\Foundation\Http\FormRequest;

class CrmNoteRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function toData(): CrmNoteData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return CrmNoteData::fromArray($validated);
    }
}
