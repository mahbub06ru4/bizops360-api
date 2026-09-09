<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Actions\ConvertLead;
use App\Modules\CRM\Actions\CreateLead;
use App\Modules\CRM\Actions\DeleteLead;
use App\Modules\CRM\Actions\MoveLeadStage;
use App\Modules\CRM\Actions\UpdateLead;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Http\Requests\ConvertLeadRequest;
use App\Modules\CRM\Http\Requests\LeadRequest;
use App\Modules\CRM\Http\Requests\MoveLeadStageRequest;
use App\Modules\CRM\Http\Resources\CustomerResource;
use App\Modules\CRM\Http\Resources\LeadResource;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query()->with('owner')->latest();

        if ($request->filled('stage')) {
            $query->where('stage', $request->string('stage')->toString());
        }

        if ($request->filled('owner_employee_id')) {
            $query->where('owner_employee_id', $request->integer('owner_employee_id'));
        }

        $this->scopeToInvolvement($query, $request);

        return LeadResource::collection($query->paginate());
    }

    /**
     * Lead counts and estimated-value totals per pipeline stage.
     */
    public function pipeline(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Lead::class);

        $query = Lead::query();
        $this->scopeToInvolvement($query, $request);

        /** @var array<string, array{count: int, value: float}> $totals */
        $totals = [];
        foreach (LeadStage::values() as $stage) {
            $totals[$stage] = ['count' => 0, 'value' => 0.0];
        }

        foreach ($query->get(['stage', 'estimated_value']) as $lead) {
            $key = $lead->stage->value;
            $totals[$key]['count']++;
            $totals[$key]['value'] += (float) $lead->estimated_value;
        }

        $summary = [];
        foreach ($totals as $stage => $row) {
            $summary[$stage] = [
                'count' => $row['count'],
                'value' => number_format($row['value'], 2, '.', ''),
            ];
        }

        return response()->json(['data' => $summary]);
    }

    public function store(LeadRequest $request, CreateLead $action): JsonResponse
    {
        $this->authorize('create', Lead::class);

        /** @var User $user */
        $user = $request->user();

        return LeadResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);

        return LeadResource::make($lead->load('owner'));
    }

    public function update(LeadRequest $request, Lead $lead, UpdateLead $action): LeadResource
    {
        $this->authorize('update', $lead);

        return LeadResource::make($action->handle($lead, $request->toData()));
    }

    public function moveStage(MoveLeadStageRequest $request, Lead $lead, MoveLeadStage $action): LeadResource
    {
        $this->authorize('update', $lead);

        return LeadResource::make($action->handle($lead, $request->toData()));
    }

    public function convert(ConvertLeadRequest $request, Lead $lead, ConvertLead $action): JsonResponse
    {
        $this->authorize('convert', $lead);

        /** @var User $user */
        $user = $request->user();

        return CustomerResource::make($action->handle($lead, $request->overrides(), $user))
            ->response()->setStatusCode(201);
    }

    public function destroy(Lead $lead, DeleteLead $action): Response
    {
        $this->authorize('delete', $lead);

        $action->handle($lead);

        return response()->noContent();
    }

    /**
     * @param  Builder<Lead>  $query
     */
    private function scopeToInvolvement(Builder $query, Request $request): void
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->can('lead.view_all')) {
            return;
        }

        $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');

        $query->where(function (Builder $q) use ($user, $employeeId): void {
            $q->where('created_by', $user->getKey());
            if ($employeeId !== null) {
                $q->orWhere('owner_employee_id', $employeeId);
            }
        });
    }
}
