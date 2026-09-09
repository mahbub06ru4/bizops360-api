<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes an employee document — both the stored file and the metadata row.
 */
class DeleteEmployeeDocument
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(EmployeeDocument $document): void
    {
        $this->assertTenantOwns($document);

        Storage::disk($document->disk)->delete($document->path);

        $document->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
