<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Billing\Http\Middleware\EnsurePlatformAdmin;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\VerifyProjectData;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Domain\VerificationDecision;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Models\VerificationReview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A platform admin approves a project's verification submission (basic
 * Pending/Verified/Rejected only, manual document review — no OCR in Phase 1;
 * see roadmap §7). Deliberately does NOT use the tenant-scoped
 * {@see InteractsWithTenant}
 * guard: a platform admin has `tenant_id = null` and reviews across every
 * tenant, so the route this action serves runs outside the `tenant`/`industry`
 * middleware entirely (see {@see EnsurePlatformAdmin}
 * for the same pattern in Billing).
 */
class VerifyProject
{
    public function handle(RealEstateProject $project, User $reviewer, VerifyProjectData $data): RealEstateProject
    {
        if ($project->status !== ProjectStatus::PendingVerification) {
            throw ValidationException::withMessages([
                'project' => "A {$project->status->value} project is not awaiting verification.",
            ]);
        }

        return DB::transaction(function () use ($project, $reviewer, $data): RealEstateProject {
            VerificationReview::query()->create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'decision' => VerificationDecision::Verified,
                'notes' => $data->notes,
                'reviewed_at' => Carbon::now(),
            ]);

            $project->status = ProjectStatus::Verified;
            $project->save();

            return $project->refresh();
        });
    }
}
