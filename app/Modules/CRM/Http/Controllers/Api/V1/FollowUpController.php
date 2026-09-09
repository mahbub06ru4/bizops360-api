<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Actions\CancelFollowUp;
use App\Modules\CRM\Actions\CompleteFollowUp;
use App\Modules\CRM\Actions\DeleteFollowUp;
use App\Modules\CRM\Actions\ScheduleFollowUp;
use App\Modules\CRM\Actions\UpdateFollowUp;
use App\Modules\CRM\Http\Requests\CompleteFollowUpRequest;
use App\Modules\CRM\Http\Requests\FollowUpRequest;
use App\Modules\CRM\Http\Resources\FollowUpResource;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FollowUpController extends Controller
{
    /**
     * All follow-ups. Filters: status, assigned_employee_id, overdue. Scoped to
     * the caller's own unless they hold follow_up.view_all.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', FollowUp::class);

        $query = FollowUp::query()->with('assignedEmployee')->orderBy('due_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('assigned_employee_id')) {
            $query->where('assigned_employee_id', $request->integer('assigned_employee_id'));
        }

        if ($request->boolean('overdue')) {
            $query->where('status', 'pending')->where('due_at', '<', now());
        }

        /** @var User $user */
        $user = $request->user();

        if (! $user->can('follow_up.view_all')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where(function (Builder $q) use ($user, $employeeId): void {
                $q->where('created_by', $user->getKey());
                if ($employeeId !== null) {
                    $q->orWhere('assigned_employee_id', $employeeId);
                }
            });
        }

        return FollowUpResource::collection($query->paginate());
    }

    public function leadIndex(Lead $lead): AnonymousResourceCollection
    {
        return $this->listFor($lead);
    }

    public function leadStore(Lead $lead, FollowUpRequest $request, ScheduleFollowUp $action): JsonResponse
    {
        return $this->storeFor($lead, $request, $action);
    }

    public function customerIndex(Customer $customer): AnonymousResourceCollection
    {
        return $this->listFor($customer);
    }

    public function customerStore(Customer $customer, FollowUpRequest $request, ScheduleFollowUp $action): JsonResponse
    {
        return $this->storeFor($customer, $request, $action);
    }

    public function update(FollowUp $followUp, FollowUpRequest $request, UpdateFollowUp $action): FollowUpResource
    {
        $this->authorize('update', $followUp);

        return FollowUpResource::make($action->handle($followUp, $request->toData()));
    }

    public function complete(FollowUp $followUp, CompleteFollowUpRequest $request, CompleteFollowUp $action): FollowUpResource
    {
        $this->authorize('update', $followUp);

        /** @var User $user */
        $user = $request->user();

        return FollowUpResource::make($action->handle($followUp, $request->toData(), $user));
    }

    public function cancel(FollowUp $followUp, CancelFollowUp $action): FollowUpResource
    {
        $this->authorize('update', $followUp);

        return FollowUpResource::make($action->handle($followUp));
    }

    public function destroy(FollowUp $followUp, DeleteFollowUp $action): Response
    {
        $this->authorize('delete', $followUp);

        $action->handle($followUp);

        return response()->noContent();
    }

    /**
     * @param  Lead|Customer  $parent
     */
    private function listFor(Model $parent): AnonymousResourceCollection
    {
        $this->authorize('view', $parent);

        return FollowUpResource::collection(
            $parent->followups()->with('assignedEmployee')->orderBy('due_at')->paginate(),
        );
    }

    /**
     * @param  Lead|Customer  $parent
     */
    private function storeFor(Model $parent, FollowUpRequest $request, ScheduleFollowUp $action): JsonResponse
    {
        $this->authorize('update', $parent);
        $this->authorize('create', FollowUp::class);

        /** @var User $user */
        $user = $request->user();

        return FollowUpResource::make($action->handle($parent, $request->toData(), $user))
            ->response()->setStatusCode(201);
    }
}
