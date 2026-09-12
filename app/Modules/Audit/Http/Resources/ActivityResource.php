<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Resources;

use App\Modules\Audit\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Activity
 */
class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'event' => $this->event,
            'description' => $this->description,
            'subject_type' => $this->subject_type !== null ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'causer' => $this->causer === null ? null : [
                'id' => $this->causer->getKey(),
                'name' => $this->causer->name ?? null,
            ],
            'changes' => $this->attribute_changes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
