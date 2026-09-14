<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Billing\Http\Middleware\EnsurePlatformAdmin;
use App\Modules\Industry\RealEstate\Actions\RejectProject;
use App\Modules\Industry\RealEstate\Actions\VerifyProject;
use App\Modules\Industry\RealEstate\Http\Requests\RejectProjectRequest;
use App\Modules\Industry\RealEstate\Http\Requests\VerifyProjectRequest;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectResource;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;

/**
 * Platform-admin-only review of a project's verification submission. Kept
 * separate from {@see ProjectController} rather than bloating it, and its
 * routes sit outside the `tenant`/`industry` gate entirely — see the route
 * file and {@see EnsurePlatformAdmin},
 * whose `platform_admin` middleware alone guards access here.
 */
class ProjectVerificationController extends Controller
{
    public function verify(VerifyProjectRequest $request, RealEstateProject $project, VerifyProject $action): ProjectResource
    {
        /** @var User $reviewer */
        $reviewer = $request->user();

        return ProjectResource::make($action->handle($project, $reviewer, $request->toData())->load('verificationReviews'));
    }

    public function reject(RejectProjectRequest $request, RealEstateProject $project, RejectProject $action): ProjectResource
    {
        /** @var User $reviewer */
        $reviewer = $request->user();

        return ProjectResource::make($action->handle($project, $reviewer, $request->toData())->load('verificationReviews'));
    }
}
