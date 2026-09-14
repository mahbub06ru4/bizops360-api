<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Metadata only — deliberately omits any public URL to the file. Never embed
 * this in {@see ProjectResource}; only serve it from a Policy-gated,
 * tenant-only endpoint.
 *
 * @mixin ProjectDocument
 */
class ProjectDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'document_type' => $this->document_type->value,
            'is_private' => $this->is_private,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
