<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Actions\CreateEmployee;
use App\Modules\Organization\Actions\DeleteEmployee;
use App\Modules\Organization\Actions\TerminateEmployee;
use App\Modules\Organization\Actions\UpdateEmployee;
use App\Modules\Organization\Http\Requests\EmployeeRequest;
use App\Modules\Organization\Http\Resources\EmployeeResource;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    /** @var list<string> */
    private const array WITH = ['branch', 'department', 'designation'];

    /**
     * List the current tenant's employees.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Employee::class);

        return EmployeeResource::collection(
            Employee::query()->with(self::WITH)->orderBy('last_name')->orderBy('first_name')->paginate(),
        );
    }

    /**
     * Create an employee.
     */
    public function store(EmployeeRequest $request, CreateEmployee $action): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $action->handle($request->toData());

        return EmployeeResource::make($employee->load(self::WITH))->response()->setStatusCode(201);
    }

    /**
     * Show a single employee.
     */
    public function show(Employee $employee): EmployeeResource
    {
        $this->authorize('view', $employee);

        return EmployeeResource::make($employee->load(self::WITH));
    }

    /**
     * Update an employee.
     */
    public function update(EmployeeRequest $request, Employee $employee, UpdateEmployee $action): EmployeeResource
    {
        $this->authorize('update', $employee);

        return EmployeeResource::make($action->handle($employee, $request->toData())->load(self::WITH));
    }

    /**
     * Terminate an employee.
     */
    public function terminate(Employee $employee, TerminateEmployee $action): EmployeeResource
    {
        $this->authorize('terminate', $employee);

        return EmployeeResource::make($action->handle($employee)->load(self::WITH));
    }

    /**
     * Delete an employee.
     */
    public function destroy(Employee $employee, DeleteEmployee $action): Response
    {
        $this->authorize('delete', $employee);

        $action->handle($employee);

        return response()->noContent();
    }
}
