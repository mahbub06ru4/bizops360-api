<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Modules\CRM\Models\CrmActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CrmActivity
 */
class CrmActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_type' => class_basename($this->subject_type),
            'subject_id' => $this->subject_id,
            'causer_id' => $this->causer_id,
            'causer_name' => $this->whenLoaded('causer', fn () => $this->causer?->name),
            'event' => $this->event,
            'description' => $this->description,
            'properties' => $this->properties,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
