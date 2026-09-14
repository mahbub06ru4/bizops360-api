<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Moves a project from `draft` (or a previously `rejected` resubmission) into
 * the `pending_verification` queue. The admin verification workflow itself
 * (approve/reject with a document checklist) is Phase 2 — this action only
 * flips the status so the seller's intent is recorded.
 */
class SubmitProjectForVerification
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project): RealEstateProject
    {
        $this->assertTenantOwns($project);

        if (! in_array($project->status, [ProjectStatus::Draft, ProjectStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'project' => "A {$project->status->value} project cannot be submitted for verification.",
            ]);
        }

        if ($project->buildings()->doesntExist() && $project->landShares()->doesntExist()) {
            throw ValidationException::withMessages([
                'project' => 'Add at least one building with units, or a land-share structure, before submitting.',
            ]);
        }

        $project->status = ProjectStatus::PendingVerification;
        $project->save();

        return $project->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
