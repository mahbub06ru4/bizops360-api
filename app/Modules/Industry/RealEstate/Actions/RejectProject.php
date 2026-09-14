<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Models\User;
use App\Modules\Industry\RealEstate\Data\RejectProjectData;
use App\Modules\Industry\RealEstate\Domain\ProjectStatus;
use App\Modules\Industry\RealEstate\Domain\VerificationDecision;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Models\VerificationReview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A platform admin rejects a project's verification submission, always with a
 * reason. The seller can amend the project and resubmit — {@see
 * \App\Modules\Industry\RealEstate\Actions\SubmitProjectForVerification}
 * already accepts a `rejected` project back into the queue.
 *
 * Same deliberate omission of {@see \App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant}
 * as {@see VerifyProject} — see that class's docblock.
 */
class RejectProject
{
    public function handle(RealEstateProject $project, User $reviewer, RejectProjectData $data): RealEstateProject
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
                'decision' => VerificationDecision::Rejected,
                'notes' => $data->notes,
                'reviewed_at' => Carbon::now(),
            ]);

            $project->status = ProjectStatus::Rejected;
            $project->save();

            return $project->refresh();
        });
    }
}
