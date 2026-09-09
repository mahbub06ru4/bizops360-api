<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Requests;

use App\Modules\Operations\Data\TaskStatusData;
use App\Modules\Operations\Domain\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTaskStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(TaskStatus::values())],
        ];
    }

    public function toData(): TaskStatusData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return TaskStatusData::fromArray($validated);
    }
}
