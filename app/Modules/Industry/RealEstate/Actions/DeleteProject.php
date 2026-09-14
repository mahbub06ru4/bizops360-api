<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project): void
    {
        $this->assertTenantOwns($project);

        if ($project->status !== ProjectStatus::Draft) {
            throw ValidationException::withMessages([
                'project' => 'Only a draft project can be deleted.',
            ]);
        }

        $project->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
