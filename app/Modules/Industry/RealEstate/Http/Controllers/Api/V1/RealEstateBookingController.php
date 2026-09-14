<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\CancelBooking;
use App\Modules\Industry\RealEstate\Actions\ConfirmBooking;
use App\Modules\Industry\RealEstate\Actions\ReserveUnit;
use App\Modules\Industry\RealEstate\Http\Resources\RealEstateBookingResource;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\RealEstateBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Reserve → book → complete for a unit purchase. Deliberately not reusing
 * Travel's `bookings` resource name or model — see {@see RealEstateBooking}.
 */
class RealEstateBookingController extends Controller
{
    /** Eager-loaded on every response so {@see RealEstateBookingResource} can embed lead/unit display labels. */
    private const DISPLAY_RELATIONS = ['lead', 'unit.building.project'];

    /** Tenant-wide booking pipeline (reservations through completed sales). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', RealEstateBooking::class);

        $query = RealEstateBooking::query()->with(self::DISPLAY_RELATIONS)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return RealEstateBookingResource::collection($query->paginate());
    }

    public function store(Request $request, Offer $offer, ReserveUnit $action): JsonResponse
    {
        $this->authorize('view', $offer);
        $this->authorize('create', RealEstateBooking::class);

        /** @var User $user */
        $user = $request->user();

        $booking = $action->handle($offer, $user);

        return RealEstateBookingResource::make($booking->load(self::DISPLAY_RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function show(RealEstateBooking $booking): RealEstateBookingResource
    {
        $this->authorize('view', $booking);

        return RealEstateBookingResource::make(
            $booking->load([...self::DISPLAY_RELATIONS, 'installmentPlan.installments'])
        );
    }

    public function confirm(Request $request, RealEstateBooking $booking, ConfirmBooking $action): RealEstateBookingResource
    {
        $this->authorize('update', $booking);

        /** @var User $user */
        $user = $request->user();

        return RealEstateBookingResource::make($action->handle($booking, $user)->load(self::DISPLAY_RELATIONS));
    }

    public function cancel(RealEstateBooking $booking, CancelBooking $action): RealEstateBookingResource
    {
        $this->authorize('update', $booking);

        return RealEstateBookingResource::make($action->handle($booking)->load(self::DISPLAY_RELATIONS));
    }
}
