<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Actions\CreateDesignation;
use App\Modules\Organization\Actions\DeleteDesignation;
use App\Modules\Organization\Actions\UpdateDesignation;
use App\Modules\Organization\Http\Requests\DesignationRequest;
use App\Modules\Organization\Http\Resources\DesignationResource;
use App\Modules\Organization\Models\Designation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DesignationController extends Controller
{
    /**
     * List the current tenant's designations.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Designation::class);

        return DesignationResource::collection(
            Designation::query()->with('department')->orderBy('rank')->orderBy('title')->paginate(),
        );
    }

    /**
     * Create a designation.
     */
    public function store(DesignationRequest $request, CreateDesignation $action): JsonResponse
    {
        $this->authorize('create', Designation::class);

        $designation = $action->handle($request->toData());

        return DesignationResource::make($designation->load('department'))->response()->setStatusCode(201);
    }

    /**
     * Show a single designation.
     */
    public function show(Designation $designation): DesignationResource
    {
        $this->authorize('view', $designation);

        return DesignationResource::make($designation->load('department'));
    }

    /**
     * Update a designation.
     */
    public function update(DesignationRequest $request, Designation $designation, UpdateDesignation $action): DesignationResource
    {
        $this->authorize('update', $designation);

        return DesignationResource::make($action->handle($designation, $request->toData())->load('department'));
    }

    /**
     * Delete a designation.
     */
    public function destroy(Designation $designation, DeleteDesignation $action): Response
    {
        $this->authorize('delete', $designation);

        $action->handle($designation);

        return response()->noContent();
    }
}
