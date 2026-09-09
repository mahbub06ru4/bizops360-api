<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Operations\Data\TaskCommentData;
use Illuminate\Foundation\Http\FormRequest;

class TaskCommentRequest extends FormRequest
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

    public function toData(): TaskCommentData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TaskCommentData::fromArray($validated);
    }
}
