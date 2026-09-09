<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Http\Resources\InvoiceResource;
use App\Modules\Industry\Travel\Actions\CancelBooking;
use App\Modules\Industry\Travel\Actions\CreateBooking;
use App\Modules\Industry\Travel\Actions\DeleteBooking;
use App\Modules\Industry\Travel\Actions\IssueBooking;
use App\Modules\Industry\Travel\Actions\RaiseInvoiceForBooking;
use App\Modules\Industry\Travel\Actions\RefundBooking;
use App\Modules\Industry\Travel\Actions\UpdateBooking;
use App\Modules\Industry\Travel\Http\Requests\BookingRequest;
use App\Modules\Industry\Travel\Http\Requests\IssueBookingRequest;
use App\Modules\Industry\Travel\Http\Requests\RaiseBookingInvoiceRequest;
use App\Modules\Industry\Travel\Http\Requests\RefundBookingRequest;
use App\Modules\Industry\Travel\Http\Resources\BookingResource;
use App\Modules\Industry\Travel\Models\Booking;
use App\Modules\Organization\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    private const string WITH = 'customer';

    /** @var list<string> */
    private const DETAIL = ['customer', 'passengers.traveller', 'segments', 'hotelStays', 'itinerary'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Booking::class);

        $query = Booking::query()->with(self::WITH)->latest();

        foreach (['status', 'type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        /** @var User $user */
        $user = $request->user();

        if (! $user->can('booking.view_all')) {
            $employeeId = Employee::query()->where('user_id', $user->getKey())->value('id');
            $query->where(function (Builder $q) use ($user, $employeeId): void {
                $q->where('created_by', $user->getKey());
                if ($employeeId !== null) {
                    $q->orWhere('handled_by_employee_id', $employeeId);
                }
            });
        }

        return BookingResource::collection($query->paginate());
    }

    public function store(BookingRequest $request, CreateBooking $action): JsonResponse
    {
        $this->authorize('create', Booking::class);

        /** @var User $user */
        $user = $request->user();

        return BookingResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Booking $booking): BookingResource
    {
        $this->authorize('view', $booking);

        return BookingResource::make($booking->load(self::DETAIL));
    }

    public function update(BookingRequest $request, Booking $booking, UpdateBooking $action): BookingResource
    {
        $this->authorize('update', $booking);

        return BookingResource::make($action->handle($booking, $request->toData()));
    }

    public function issue(IssueBookingRequest $request, Booking $booking, IssueBooking $action): BookingResource
    {
        $this->authorize('issue', $booking);

        return BookingResource::make($action->handle(
            $booking,
            $request->string('pnr')->toString() ?: null,
            $request->string('issued_on')->toString() ?: null,
        ));
    }

    public function cancel(Request $request, Booking $booking, CancelBooking $action): BookingResource
    {
        $this->authorize('cancel', $booking);

        return BookingResource::make(
            $action->handle($booking, $request->string('reason')->toString() ?: null),
        );
    }

    public function refund(RefundBookingRequest $request, Booking $booking, RefundBooking $action): BookingResource
    {
        $this->authorize('refund', $booking);

        return BookingResource::make($action->handle($booking, $request->toData()));
    }

    public function invoice(RaiseBookingInvoiceRequest $request, Booking $booking, RaiseInvoiceForBooking $action): JsonResponse
    {
        $this->authorize('invoice', $booking);

        /** @var User $user */
        $user = $request->user();

        $booking = $action->handle(
            $booking,
            $user,
            $request->string('issue_date')->toString() ?: null,
            $request->string('due_date')->toString() ?: null,
        );

        return InvoiceResource::make($booking->invoice()->firstOrFail())
            ->response()->setStatusCode(201);
    }

    public function destroy(Booking $booking, DeleteBooking $action): Response
    {
        $this->authorize('delete', $booking);

        $action->handle($booking);

        return response()->noContent();
    }
}
