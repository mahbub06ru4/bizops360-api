<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Organization\Actions\AssignUserRoles;
use App\Modules\Organization\Actions\CreateUser;
use App\Modules\Organization\Actions\DeleteUser;
use App\Modules\Organization\Actions\UpdateUser;
use App\Modules\Organization\Http\Requests\AssignUserRolesRequest;
use App\Modules\Organization\Http\Requests\StoreUserRequest;
use App\Modules\Organization\Http\Requests\UpdateUserRequest;
use App\Modules\Organization\Http\Resources\TenantUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * List the current tenant's users.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        return TenantUserResource::collection(
            User::query()->with('roles')->orderBy('name')->paginate(),
        );
    }

    /**
     * Add a user to the current tenant.
     */
    public function store(StoreUserRequest $request, CreateUser $action): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $action->handle($request->toData());

        return TenantUserResource::make($user)->response()->setStatusCode(201);
    }

    /**
     * Show a single tenant user.
     */
    public function show(User $user): TenantUserResource
    {
        $this->authorize('view', $user);

        return TenantUserResource::make($user->load('roles'));
    }

    /**
     * Update a tenant user's profile.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): TenantUserResource
    {
        $this->authorize('update', $user);

        return TenantUserResource::make($action->handle($user, $request->toData()));
    }

    /**
     * Replace the role set assigned to a tenant user.
     */
    public function assignRoles(AssignUserRolesRequest $request, User $user, AssignUserRoles $action): TenantUserResource
    {
        $this->authorize('assignRoles', $user);

        return TenantUserResource::make($action->handle($user, $request->toData()));
    }

    /**
     * Remove a user from the current tenant.
     */
    public function destroy(Request $request, User $user, DeleteUser $action): Response
    {
        $this->authorize('delete', $user);

        /** @var User $actor */
        $actor = $request->user();
        $action->handle($user, $actor);

        return response()->noContent();
    }
}
