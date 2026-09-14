<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Actions\AcceptOffer;
use App\Modules\Industry\RealEstate\Actions\CounterOffer;
use App\Modules\Industry\RealEstate\Actions\MakeOffer;
use App\Modules\Industry\RealEstate\Actions\RejectOffer;
use App\Modules\Industry\RealEstate\Http\Requests\CounterOfferRequest;
use App\Modules\Industry\RealEstate\Http\Requests\MakeOfferRequest;
use App\Modules\Industry\RealEstate\Http\Resources\OfferResource;
use App\Modules\Industry\RealEstate\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    /** Eager-loaded on every response so {@see OfferResource} can embed lead/unit display labels. */
    private const DISPLAY_RELATIONS = ['lead', 'unit.building.project'];

    /**
     * Tenant-wide negotiation queue (across every lead). `latest_per_thread`
     * (default true) collapses each offer/counter-offer chain down to its
     * current head — a row nothing else points at via `previous_offer_id` —
     * so the list reads as "one row per active negotiation", not every
     * historical counter. Pass `latest_per_thread=false` to see every row.
     */
    public function all(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Offer::class);

        $query = Offer::query()->with(self::DISPLAY_RELATIONS)->latest();

        if ($request->boolean('latest_per_thread', true)) {
            $query->whereNotIn('id', function ($sub): void {
                $sub->select('previous_offer_id')
                    ->from('offers')
                    ->whereNotNull('previous_offer_id');
            });
        }

        return OfferResource::collection($query->paginate());
    }

    public function index(Lead $lead): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Offer::class);

        return OfferResource::collection(
            Offer::query()->with(self::DISPLAY_RELATIONS)->where('lead_id', $lead->getKey())->latest()->get()
        );
    }

    public function store(MakeOfferRequest $request, Lead $lead, MakeOffer $action): JsonResponse
    {
        $this->authorize('create', Offer::class);

        /** @var User $user */
        $user = $request->user();

        $offer = $action->handle($lead, $request->toData(), $user);

        return OfferResource::make($offer->load(self::DISPLAY_RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function show(Offer $offer): OfferResource
    {
        $this->authorize('view', $offer);

        return OfferResource::make($offer->load([...self::DISPLAY_RELATIONS, 'counterOffers']));
    }

    public function counter(CounterOfferRequest $request, Offer $offer, CounterOffer $action): JsonResponse
    {
        $this->authorize('update', $offer);

        $counter = $action->handle($offer, $request->toData());

        return OfferResource::make($counter->load(self::DISPLAY_RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function accept(Offer $offer, AcceptOffer $action): OfferResource
    {
        $this->authorize('update', $offer);

        return OfferResource::make($action->handle($offer)->load(self::DISPLAY_RELATIONS));
    }

    public function reject(Offer $offer, RejectOffer $action): OfferResource
    {
        $this->authorize('update', $offer);

        return OfferResource::make($action->handle($offer)->load(self::DISPLAY_RELATIONS));
    }
}
