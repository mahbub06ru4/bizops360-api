<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Models\User;
use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Data\StoreEmployeeDocumentData;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Stores an uploaded file on the configured disk under a tenant/employee-scoped
 * path and records its metadata.
 */
class UploadEmployeeDocument
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(StoreEmployeeDocumentData $data, UploadedFile $file, User $uploader): EmployeeDocument
    {
        $this->assertReferenceOwned($data->employeeId, Employee::class);

        $tenantId = $this->currentTenantId();
        $disk = (string) config('filesystems.default');

        $path = $file->store("tenants/{$tenantId}/employees/{$data->employeeId}/documents", $disk);

        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'The document could not be stored.']);
        }

        $document = new EmployeeDocument([
            'category' => $data->category,
            'title' => $data->title,
            'expires_at' => $data->expiresAt,
        ]);
        $document->tenant_id = $tenantId;
        $document->employee_id = $data->employeeId;
        $document->disk = $disk;
        $document->path = $path;
        $document->original_name = $file->getClientOriginalName();
        $document->mime_type = $file->getClientMimeType();
        $document->size = (int) ($file->getSize() ?: 0);
        $document->uploaded_by = $uploader->getKey();
        $document->save();

        return $document->load('employee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
