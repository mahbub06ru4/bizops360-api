<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\Travel\Actions\DeleteTraveller;
use App\Modules\Industry\Travel\Actions\RegisterTraveller;
use App\Modules\Industry\Travel\Actions\UpdateTraveller;
use App\Modules\Industry\Travel\Http\Requests\TravellerRequest;
use App\Modules\Industry\Travel\Http\Resources\TravellerResource;
use App\Modules\Industry\Travel\Models\Traveller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TravellerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Traveller::class);

        $query = Traveller::query()->with('customer')->latest();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($q) use ($term): void {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('passport_number', 'like', "%{$term}%");
            });
        }

        return TravellerResource::collection($query->paginate());
    }

    public function store(TravellerRequest $request, RegisterTraveller $action): JsonResponse
    {
        $this->authorize('create', Traveller::class);

        /** @var User $user */
        $user = $request->user();

        return TravellerResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Traveller $traveller): TravellerResource
    {
        $this->authorize('view', $traveller);

        return TravellerResource::make($traveller->load('customer'));
    }

    public function update(TravellerRequest $request, Traveller $traveller, UpdateTraveller $action): TravellerResource
    {
        $this->authorize('update', $traveller);

        return TravellerResource::make($action->handle($traveller, $request->toData()));
    }

    public function destroy(Traveller $traveller, DeleteTraveller $action): Response
    {
        $this->authorize('delete', $traveller);

        $action->handle($traveller);

        return response()->noContent();
    }
}
