<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\ProjectData;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Str;

/**
 * A seller starts a new project. Always begins `draft` — nothing is visible
 * to buyers until {@see SubmitProjectForVerification} and the admin review
 * queue (Phase 2) mark it `verified`.
 */
class CreateProject
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(ProjectData $data, User $creator): RealEstateProject
    {
        $tenantId = $this->currentTenantId();

        $project = new RealEstateProject($data->toAttributes());
        $project->tenant_id = $tenantId;
        $project->created_by = $creator->getKey();
        $project->slug = $this->uniqueSlug($tenantId, $data->name);
        $project->status = ProjectStatus::Draft;
        $project->save();

        return $project;
    }

    private function uniqueSlug(int $tenantId, string $name): string
    {
        $base = Str::slug($name) ?: 'project';
        $slug = $base;
        $suffix = 1;

        while (
            RealEstateProject::query()
                ->where('tenant_id', $tenantId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
