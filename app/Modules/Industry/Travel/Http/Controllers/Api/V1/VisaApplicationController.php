<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\Travel\Actions\AddVisaRequirement;
use App\Modules\Industry\Travel\Actions\CancelVisaApplication;
use App\Modules\Industry\Travel\Actions\DeleteVisaApplication;
use App\Modules\Industry\Travel\Actions\MoveVisaToProcessing;
use App\Modules\Industry\Travel\Actions\OpenVisaApplication;
use App\Modules\Industry\Travel\Actions\RecordVisaDecision;
use App\Modules\Industry\Travel\Actions\SubmitVisaApplication;
use App\Modules\Industry\Travel\Actions\ToggleVisaRequirement;
use App\Modules\Industry\Travel\Actions\UpdateVisaApplication;
use App\Modules\Industry\Travel\Http\Requests\RecordVisaDecisionRequest;
use App\Modules\Industry\Travel\Http\Requests\SubmitVisaApplicationRequest;
use App\Modules\Industry\Travel\Http\Requests\ToggleVisaRequirementRequest;
use App\Modules\Industry\Travel\Http\Requests\VisaApplicationRequest;
use App\Modules\Industry\Travel\Http\Requests\VisaRequirementRequest;
use App\Modules\Industry\Travel\Http\Resources\VisaApplicationResource;
use App\Modules\Industry\Travel\Http\Resources\VisaRequirementResource;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Industry\Travel\Models\VisaRequirement;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class VisaApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', VisaApplication::class);

        $query = VisaApplication::query()->with(['traveller', 'requirements'])->latest();

        if ($request->filled('stage')) {
            $query->where('stage', $request->string('stage')->toString());
        }

        if ($request->filled('traveller_id')) {
            $query->where('traveller_id', $request->integer('traveller_id'));
        }

        /** @var User $user */
        $user = $request->user();

        if (! $user->can('visa_application.view_all')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where(function (Builder $q) use ($user, $employeeId): void {
                $q->where('created_by', $user->getKey());
                if ($employeeId !== null) {
                    $q->orWhere('assigned_employee_id', $employeeId);
                }
            });
        }

        return VisaApplicationResource::collection($query->paginate());
    }

    public function store(VisaApplicationRequest $request, OpenVisaApplication $action): JsonResponse
    {
        $this->authorize('create', VisaApplication::class);

        /** @var User $user */
        $user = $request->user();

        return VisaApplicationResource::make(
            $action->handle($request->toData(), $user, $request->requirements()),
        )->response()->setStatusCode(201);
    }

    public function show(VisaApplication $visaApplication): VisaApplicationResource
    {
        $this->authorize('view', $visaApplication);

        return VisaApplicationResource::make($visaApplication->load(['traveller', 'requirements']));
    }

    public function update(VisaApplicationRequest $request, VisaApplication $visaApplication, UpdateVisaApplication $action): VisaApplicationResource
    {
        $this->authorize('update', $visaApplication);

        return VisaApplicationResource::make($action->handle($visaApplication, $request->toData()));
    }

    public function addRequirement(VisaRequirementRequest $request, VisaApplication $visaApplication, AddVisaRequirement $action): JsonResponse
    {
        $this->authorize('update', $visaApplication);

        return VisaRequirementResource::make($action->handle($visaApplication, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function toggleRequirement(ToggleVisaRequirementRequest $request, VisaRequirement $visaRequirement, ToggleVisaRequirement $action): VisaRequirementResource
    {
        $application = $visaRequirement->visaApplication()->firstOrFail();
        $this->authorize('update', $application);

        return VisaRequirementResource::make($action->handle(
            $visaRequirement,
            $request->boolean('collected'),
            $request->string('collected_on')->toString() ?: null,
            $request->string('note')->toString() ?: null,
        ));
    }

    public function submit(SubmitVisaApplicationRequest $request, VisaApplication $visaApplication, SubmitVisaApplication $action): VisaApplicationResource
    {
        $this->authorize('submit', $visaApplication);

        return VisaApplicationResource::make($action->handle(
            $visaApplication,
            $request->string('submitted_on')->toString() ?: null,
            $request->string('application_no')->toString() ?: null,
        ));
    }

    public function processing(VisaApplication $visaApplication, MoveVisaToProcessing $action): VisaApplicationResource
    {
        $this->authorize('submit', $visaApplication);

        return VisaApplicationResource::make($action->handle($visaApplication));
    }

    public function decision(RecordVisaDecisionRequest $request, VisaApplication $visaApplication, RecordVisaDecision $action): VisaApplicationResource
    {
        $this->authorize('decide', $visaApplication);

        return VisaApplicationResource::make($action->handle($visaApplication, $request->toData()));
    }

    public function cancel(Request $request, VisaApplication $visaApplication, CancelVisaApplication $action): VisaApplicationResource
    {
        $this->authorize('submit', $visaApplication);

        return VisaApplicationResource::make(
            $action->handle($visaApplication, $request->string('reason')->toString() ?: null),
        );
    }

    public function destroy(VisaApplication $visaApplication, DeleteVisaApplication $action): Response
    {
        $this->authorize('delete', $visaApplication);

        $action->handle($visaApplication);

        return response()->noContent();
    }
}
