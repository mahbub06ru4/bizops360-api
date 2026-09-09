<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Resources;

use App\Modules\Operations\Models\TaskActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TaskActivity
 */
class TaskActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'causer_id' => $this->causer_id,
            'causer_name' => $this->whenLoaded('causer', fn () => $this->causer?->name),
            'event' => $this->event,
            'description' => $this->description,
            'properties' => $this->properties,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
