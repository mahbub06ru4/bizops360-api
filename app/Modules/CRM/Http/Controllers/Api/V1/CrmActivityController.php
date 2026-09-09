<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\CRM\Actions\AddCrmNote;
use App\Modules\CRM\Http\Requests\CrmNoteRequest;
use App\Modules\CRM\Http\Resources\CrmActivityResource;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CrmActivityController extends Controller
{
    public function leadIndex(Lead $lead): AnonymousResourceCollection
    {
        return $this->listFor($lead);
    }

    public function leadNote(Lead $lead, CrmNoteRequest $request, AddCrmNote $action): JsonResponse
    {
        return $this->noteFor($lead, $request, $action);
    }

    public function customerIndex(Customer $customer): AnonymousResourceCollection
    {
        return $this->listFor($customer);
    }

    public function customerNote(Customer $customer, CrmNoteRequest $request, AddCrmNote $action): JsonResponse
    {
        return $this->noteFor($customer, $request, $action);
    }

    /**
     * @param  Lead|Customer  $subject
     */
    private function listFor(Model $subject): AnonymousResourceCollection
    {
        $this->authorize('view', $subject);

        return CrmActivityResource::collection(
            $subject->activities()->with('causer')->latest('id')->paginate(),
        );
    }

    /**
     * @param  Lead|Customer  $subject
     */
    private function noteFor(Model $subject, CrmNoteRequest $request, AddCrmNote $action): JsonResponse
    {
        $this->authorize('update', $subject);

        /** @var User $user */
        $user = $request->user();

        return CrmActivityResource::make($action->handle($subject, $request->toData(), $user))
            ->response()->setStatusCode(201);
    }
}
