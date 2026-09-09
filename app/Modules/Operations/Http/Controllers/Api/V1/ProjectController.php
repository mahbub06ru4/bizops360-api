<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Operations\Actions\CreateProject;
use App\Modules\Operations\Actions\DeleteProject;
use App\Modules\Operations\Actions\UpdateProject;
use App\Modules\Operations\Http\Requests\ProjectRequest;
use App\Modules\Operations\Http\Resources\ProjectResource;
use App\Modules\Operations\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::query()->withCount('tasks')->with(['department', 'lead'])->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return ProjectResource::collection($query->paginate());
    }

    public function store(ProjectRequest $request, CreateProject $action): JsonResponse
    {
        $this->authorize('create', Project::class);

        /** @var User $user */
        $user = $request->user();

        return ProjectResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return ProjectResource::make($project->loadCount('tasks')->load(['department', 'lead']));
    }

    public function update(ProjectRequest $request, Project $project, UpdateProject $action): ProjectResource
    {
        $this->authorize('update', $project);

        return ProjectResource::make($action->handle($project, $request->toData())->loadCount('tasks'));
    }

    public function destroy(Project $project, DeleteProject $action): Response
    {
        $this->authorize('delete', $project);

        $action->handle($project);

        return response()->noContent();
    }
}
