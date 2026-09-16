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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    /** @var list<string> */
    private const array WITH = ['branch', 'department', 'designation'];

    private const int DEFAULT_PER_PAGE = 15;

    private const int MAX_PER_PAGE = 100;

    /**
     * List the current tenant's employees. Accepts `per_page` (1-100, default 15)
     * and `q` (matches name, employee code, or email).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::query()->with(self::WITH);

        if ($request->filled('q')) {
            $this->applySearch($query, $request->string('q')->toString());
        }

        return EmployeeResource::collection(
            $query->orderBy('last_name')->orderBy('first_name')->paginate($this->perPage($request)),
        );
    }

    /**
     * @param  Builder<Employee>  $query
     */
    private function applySearch(Builder $query, string $term): void
    {
        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        $query->where(function (Builder $q) use ($term): void {
            $q->whereRaw("first_name || ' ' || last_name ilike ?", [$term])
                ->orWhere('employee_code', 'ilike', $term)
                ->orWhere('email', 'ilike', $term);
        });
    }

    private function perPage(Request $request): int
    {
        $requested = $request->integer('per_page', self::DEFAULT_PER_PAGE);

        return min(max($requested, 1), self::MAX_PER_PAGE);
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
