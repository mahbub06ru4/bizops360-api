<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Actions\CreateInvoice;
use App\Modules\Finance\Actions\DeleteInvoice;
use App\Modules\Finance\Actions\RecordInvoicePayment;
use App\Modules\Finance\Actions\RefundInvoice;
use App\Modules\Finance\Actions\SendInvoice;
use App\Modules\Finance\Actions\UpdateInvoice;
use App\Modules\Finance\Actions\VoidInvoice;
use App\Modules\Finance\Http\Requests\InvoiceRequest;
use App\Modules\Finance\Http\Requests\RecordInvoicePaymentRequest;
use App\Modules\Finance\Http\Requests\RefundInvoiceRequest;
use App\Modules\Finance\Http\Resources\InvoicePaymentResource;
use App\Modules\Finance\Http\Resources\InvoiceRefundResource;
use App\Modules\Finance\Http\Resources\InvoiceResource;
use App\Modules\Finance\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with('customer')->latest('issue_date')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        return InvoiceResource::collection($query->paginate());
    }

    public function store(InvoiceRequest $request, CreateInvoice $action): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        /** @var User $user */
        $user = $request->user();

        return InvoiceResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return InvoiceResource::make($invoice->load(['customer', 'payments', 'refunds']));
    }

    public function update(InvoiceRequest $request, Invoice $invoice, UpdateInvoice $action): InvoiceResource
    {
        $this->authorize('update', $invoice);

        return InvoiceResource::make($action->handle($invoice, $request->toData()));
    }

    public function send(Invoice $invoice, SendInvoice $action): InvoiceResource
    {
        $this->authorize('send', $invoice);

        return InvoiceResource::make($action->handle($invoice));
    }

    public function void(Invoice $invoice, VoidInvoice $action): InvoiceResource
    {
        $this->authorize('void', $invoice);

        return InvoiceResource::make($action->handle($invoice));
    }

    public function destroy(Invoice $invoice, DeleteInvoice $action): Response
    {
        $this->authorize('delete', $invoice);

        $action->handle($invoice);

        return response()->noContent();
    }

    public function payments(Invoice $invoice): AnonymousResourceCollection
    {
        $this->authorize('view', $invoice);

        return InvoicePaymentResource::collection(
            $invoice->payments()->latest('paid_on')->latest('id')->get(),
        );
    }

    public function recordPayment(
        RecordInvoicePaymentRequest $request,
        Invoice $invoice,
        RecordInvoicePayment $action,
    ): JsonResponse {
        $this->authorize('recordPayment', $invoice);

        /** @var User $user */
        $user = $request->user();

        return InvoicePaymentResource::make($action->handle($invoice, $request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function refund(
        RefundInvoiceRequest $request,
        Invoice $invoice,
        RefundInvoice $action,
    ): JsonResponse {
        $this->authorize('refund', $invoice);

        /** @var User $user */
        $user = $request->user();

        return InvoiceRefundResource::make($action->handle($invoice, $request->toData(), $user))
            ->response()->setStatusCode(201);
    }
}
