<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Actions\CreateTeam;
use App\Modules\Organization\Actions\DeleteTeam;
use App\Modules\Organization\Actions\SetTeamMembers;
use App\Modules\Organization\Actions\UpdateTeam;
use App\Modules\Organization\Http\Requests\TeamMembersRequest;
use App\Modules\Organization\Http\Requests\TeamRequest;
use App\Modules\Organization\Http\Resources\TeamResource;
use App\Modules\Organization\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    /**
     * List the current tenant's teams.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Team::class);

        return TeamResource::collection(
            Team::query()->withCount('members')->orderBy('name')->paginate(),
        );
    }

    /**
     * Create a team.
     */
    public function store(TeamRequest $request, CreateTeam $action): JsonResponse
    {
        $this->authorize('create', Team::class);

        $team = $action->handle($request->toData());

        return TeamResource::make($team->loadCount('members'))->response()->setStatusCode(201);
    }

    /**
     * Show a single team with its members.
     */
    public function show(Team $team): TeamResource
    {
        $this->authorize('view', $team);

        return TeamResource::make($team->load('members')->loadCount('members'));
    }

    /**
     * Update a team.
     */
    public function update(TeamRequest $request, Team $team, UpdateTeam $action): TeamResource
    {
        $this->authorize('update', $team);

        return TeamResource::make($action->handle($team, $request->toData())->loadCount('members'));
    }

    /**
     * Replace a team's member list.
     */
    public function setMembers(TeamMembersRequest $request, Team $team, SetTeamMembers $action): TeamResource
    {
        $this->authorize('update', $team);

        return TeamResource::make($action->handle($team, $request->toData())->loadCount('members'));
    }

    /**
     * Delete a team.
     */
    public function destroy(Team $team, DeleteTeam $action): Response
    {
        $this->authorize('delete', $team);

        $action->handle($team);

        return response()->noContent();
    }
}
