<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\AddLandRecord;
use App\Modules\Industry\RealEstate\Actions\AddLandShare;
use App\Modules\Industry\RealEstate\Actions\AddPaymentPlan;
use App\Modules\Industry\RealEstate\Actions\AddProjectLocation;
use App\Modules\Industry\RealEstate\Actions\CreateProject;
use App\Modules\Industry\RealEstate\Actions\DeleteProject;
use App\Modules\Industry\RealEstate\Actions\SetProjectPricing;
use App\Modules\Industry\RealEstate\Actions\SubmitProjectForVerification;
use App\Modules\Industry\RealEstate\Actions\UpdateProject;
use App\Modules\Industry\RealEstate\Http\Requests\LandRecordRequest;
use App\Modules\Industry\RealEstate\Http\Requests\LandShareRequest;
use App\Modules\Industry\RealEstate\Http\Requests\PaymentPlanRequest;
use App\Modules\Industry\RealEstate\Http\Requests\ProjectLocationRequest;
use App\Modules\Industry\RealEstate\Http\Requests\ProjectPricingRequest;
use App\Modules\Industry\RealEstate\Http\Requests\ProjectRequest;
use App\Modules\Industry\RealEstate\Http\Resources\LandRecordResource;
use App\Modules\Industry\RealEstate\Http\Resources\LandShareResource;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectPaymentPlanResource;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectLocationResource;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectPricingResource;
use App\Modules\Industry\RealEstate\Http\Resources\ProjectResource;
use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * The seller's project workspace and the buyer's project detail view (Phase 0
 * definition of done: a seller creates a fully structured project — including
 * land-share support — and it renders correctly on a buyer detail screen).
 */
class ProjectController extends Controller
{
    /** @var list<string> */
    private const DETAIL = ['locations', 'amenities', 'pricing', 'paymentPlans', 'buildings.units.media', 'buildings.units.prices', 'landShares'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', RealEstateProject::class);

        $query = RealEstateProject::query()->with(['locations', 'pricing'])->latest();

        foreach (['status', 'project_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        return ProjectResource::collection($query->paginate());
    }

    public function store(ProjectRequest $request, CreateProject $action): JsonResponse
    {
        $this->authorize('create', RealEstateProject::class);

        /** @var User $user */
        $user = $request->user();

        return ProjectResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(RealEstateProject $project): ProjectResource
    {
        $this->authorize('view', $project);

        return ProjectResource::make($project->load(self::DETAIL));
    }

    public function update(ProjectRequest $request, RealEstateProject $project, UpdateProject $action): ProjectResource
    {
        $this->authorize('update', $project);

        return ProjectResource::make($action->handle($project, $request->toData())->load(self::DETAIL));
    }

    public function destroy(RealEstateProject $project, DeleteProject $action): Response
    {
        $this->authorize('delete', $project);

        $action->handle($project);

        return response()->noContent();
    }

    public function submit(RealEstateProject $project, SubmitProjectForVerification $action): ProjectResource
    {
        $this->authorize('submit', $project);

        return ProjectResource::make($action->handle($project));
    }

    public function addLocation(ProjectLocationRequest $request, RealEstateProject $project, AddProjectLocation $action): JsonResponse
    {
        $this->authorize('update', $project);

        return ProjectLocationResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function setPricing(ProjectPricingRequest $request, RealEstateProject $project, SetProjectPricing $action): JsonResponse
    {
        $this->authorize('update', $project);

        return ProjectPricingResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function addPaymentPlan(PaymentPlanRequest $request, RealEstateProject $project, AddPaymentPlan $action): JsonResponse
    {
        $this->authorize('update', $project);

        return ProjectPaymentPlanResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    public function addLandShare(LandShareRequest $request, RealEstateProject $project, AddLandShare $action): JsonResponse
    {
        $this->authorize('update', $project);

        return LandShareResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }

    /**
     * Legal land reference — admin-only (see LandRecordPolicy), regardless of
     * whether the caller can otherwise update the project.
     */
    public function addLandRecord(LandRecordRequest $request, RealEstateProject $project, AddLandRecord $action): JsonResponse
    {
        $this->authorize('create', LandRecord::class);
        $this->authorize('update', $project);

        return LandRecordResource::make($action->handle($project, $request->toData()))
            ->response()->setStatusCode(201);
    }
}
