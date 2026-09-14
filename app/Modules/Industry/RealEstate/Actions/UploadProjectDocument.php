<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\ProjectDocumentData;
use App\Modules\Industry\RealEstate\Models\ProjectDocument;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Stores a private project document (RAJUK approval, land deed, mutation, …)
 * on the configured disk under a tenant/project-scoped path. Never surface
 * this file through a public URL — see {@see \App\Modules\Industry\RealEstate\Policies\ProjectDocumentPolicy}.
 */
class UploadProjectDocument
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, ProjectDocumentData $data, UploadedFile $file, User $uploader): ProjectDocument
    {
        $this->assertTenantOwns($project);

        $tenantId = (int) $project->tenant_id;
        $disk = (string) config('filesystems.default');

        $path = $file->store("tenants/{$tenantId}/real-estate/projects/{$project->getKey()}/documents", $disk);

        if (! is_string($path)) {
            throw ValidationException::withMessages(['file' => 'The document could not be stored.']);
        }

        $document = new ProjectDocument([
            'document_type' => $data->documentType,
            'is_private' => $data->isPrivate,
        ]);
        $document->tenant_id = $tenantId;
        $document->project_id = $project->getKey();
        $document->file_path = $path;
        $document->uploaded_by = $uploader->getKey();
        $document->save();

        return $document;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
