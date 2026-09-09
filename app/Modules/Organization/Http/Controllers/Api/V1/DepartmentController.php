<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Actions\CreateDepartment;
use App\Modules\Organization\Actions\DeleteDepartment;
use App\Modules\Organization\Actions\UpdateDepartment;
use App\Modules\Organization\Http\Requests\DepartmentRequest;
use App\Modules\Organization\Http\Resources\DepartmentResource;
use App\Modules\Organization\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DepartmentController extends Controller
{
    /**
     * List the current tenant's departments.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Department::class);

        return DepartmentResource::collection(
            Department::query()->with('branch')->orderBy('name')->paginate(),
        );
    }

    /**
     * Create a department.
     */
    public function store(DepartmentRequest $request, CreateDepartment $action): JsonResponse
    {
        $this->authorize('create', Department::class);

        $department = $action->handle($request->toData());

        return DepartmentResource::make($department->load('branch'))->response()->setStatusCode(201);
    }

    /**
     * Show a single department.
     */
    public function show(Department $department): DepartmentResource
    {
        $this->authorize('view', $department);

        return DepartmentResource::make($department->load('branch'));
    }

    /**
     * Update a department.
     */
    public function update(DepartmentRequest $request, Department $department, UpdateDepartment $action): DepartmentResource
    {
        $this->authorize('update', $department);

        return DepartmentResource::make($action->handle($department, $request->toData())->load('branch'));
    }

    /**
     * Delete a department.
     */
    public function destroy(Department $department, DeleteDepartment $action): Response
    {
        $this->authorize('delete', $department);

        $action->handle($department);

        return response()->noContent();
    }
}
