<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\UploadProjectDocument;
use App\Modules\Industry\RealEstate\Http\Requests\ProjectDocumentRequest;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectDocumentResource;
use App\Modules\Industry\RealEstate\Models\ProjectDocument;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * Private project documents (RAJUK approval, land deed, mutation, …).
 * Tenant-only: no public listing, and deliberately no download action yet —
 * Phase 0 only needs custody, not signed-URL delivery.
 */
class ProjectDocumentController extends Controller
{
    public function index(RealEstateProject $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ProjectDocument::class);
        $this->authorize('view', $project);

        return ProjectDocumentResource::collection(
            $project->documents()->latest()->paginate(),
        );
    }

    public function store(ProjectDocumentRequest $request, RealEstateProject $project, UploadProjectDocument $action): JsonResponse
    {
        $this->authorize('create', ProjectDocument::class);
        $this->authorize('update', $project);

        /** @var User $user */
        $user = $request->user();
        /** @var UploadedFile $file */
        $file = $request->file('file');

        return ProjectDocumentResource::make($action->handle($project, $request->toData(), $file, $user))
            ->response()->setStatusCode(201);
    }
}
