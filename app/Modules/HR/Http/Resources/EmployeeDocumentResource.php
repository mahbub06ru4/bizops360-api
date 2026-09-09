<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Resources;

use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * @mixin EmployeeDocument
 */
class EmployeeDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'category' => $this->category->value,
            'title' => $this->title,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'expires_at' => $this->expires_at?->toDateString(),
            'is_expired' => $this->isExpired(),
            'uploaded_by' => $this->uploaded_by,
            'download_url' => URL::temporarySignedRoute(
                'api.v1.employee-documents.file',
                Carbon::now()->addMinutes(15),
                ['employeeDocument' => $this->id],
            ),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
