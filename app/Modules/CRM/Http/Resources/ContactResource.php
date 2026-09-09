<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Modules\CRM\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contact
 */
class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contactable_type' => class_basename($this->contactable_type),
            'contactable_id' => $this->contactable_id,
            'name' => $this->name,
            'title' => $this->title,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_primary' => $this->is_primary,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
