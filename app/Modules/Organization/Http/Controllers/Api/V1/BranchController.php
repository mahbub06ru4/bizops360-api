<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Actions\CreateBranch;
use App\Modules\Organization\Actions\DeleteBranch;
use App\Modules\Organization\Actions\UpdateBranch;
use App\Modules\Organization\Http\Requests\BranchRequest;
use App\Modules\Organization\Http\Resources\BranchResource;
use App\Modules\Organization\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BranchController extends Controller
{
    /**
     * List the current tenant's branches.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Branch::class);

        return BranchResource::collection(
            Branch::query()->orderBy('name')->paginate(),
        );
    }

    /**
     * Create a branch.
     */
    public function store(BranchRequest $request, CreateBranch $action): JsonResponse
    {
        $this->authorize('create', Branch::class);

        $branch = $action->handle($request->toData());

        return BranchResource::make($branch)->response()->setStatusCode(201);
    }

    /**
     * Show a single branch.
     */
    public function show(Branch $branch): BranchResource
    {
        $this->authorize('view', $branch);

        return BranchResource::make($branch);
    }

    /**
     * Update a branch.
     */
    public function update(BranchRequest $request, Branch $branch, UpdateBranch $action): BranchResource
    {
        $this->authorize('update', $branch);

        return BranchResource::make($action->handle($branch, $request->toData()));
    }

    /**
     * Delete a branch.
     */
    public function destroy(Branch $branch, DeleteBranch $action): Response
    {
        $this->authorize('delete', $branch);

        $action->handle($branch);

        return response()->noContent();
    }
}
