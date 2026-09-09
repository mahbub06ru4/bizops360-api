<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Actions\BuildCustomerHistory;
use App\Modules\CRM\Actions\CreateCustomer;
use App\Modules\CRM\Actions\DeleteCustomer;
use App\Modules\CRM\Actions\UpdateCustomer;
use App\Modules\CRM\Http\Requests\CustomerRequest;
use App\Modules\CRM\Http\Resources\CustomerHistoryResource;
use App\Modules\CRM\Http\Resources\CustomerResource;
use App\Modules\CRM\Models\Customer;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->with('owner')->latest();

        if ($request->filled('owner_employee_id')) {
            $query->where('owner_employee_id', $request->integer('owner_employee_id'));
        }

        /** @var User $user */
        $user = $request->user();

        if (! $user->can('customer.view_all')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where(function (Builder $q) use ($user, $employeeId): void {
                $q->where('created_by', $user->getKey());
                if ($employeeId !== null) {
                    $q->orWhere('owner_employee_id', $employeeId);
                }
            });
        }

        return CustomerResource::collection($query->paginate());
    }

    public function store(CustomerRequest $request, CreateCustomer $action): JsonResponse
    {
        $this->authorize('create', Customer::class);

        /** @var User $user */
        $user = $request->user();

        return CustomerResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return CustomerResource::make($customer->load('owner'));
    }

    /**
     * The customer's full CRM history: profile, originating lead, contacts,
     * follow-ups and activity timeline.
     */
    public function history(Customer $customer, BuildCustomerHistory $action): CustomerHistoryResource
    {
        $this->authorize('view', $customer);

        return CustomerHistoryResource::make($action->handle($customer));
    }

    public function update(CustomerRequest $request, Customer $customer, UpdateCustomer $action): CustomerResource
    {
        $this->authorize('update', $customer);

        return CustomerResource::make($action->handle($customer, $request->toData()));
    }

    public function destroy(Customer $customer, DeleteCustomer $action): Response
    {
        $this->authorize('delete', $customer);

        $action->handle($customer);

        return response()->noContent();
    }
}
