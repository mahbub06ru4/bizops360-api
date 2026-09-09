<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Actions\AddContact;
use App\Modules\CRM\Actions\DeleteContact;
use App\Modules\CRM\Actions\UpdateContact;
use App\Modules\CRM\Http\Requests\ContactRequest;
use App\Modules\CRM\Http\Resources\ContactResource;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function leadIndex(Lead $lead): AnonymousResourceCollection
    {
        return $this->listFor($lead);
    }

    public function leadStore(Lead $lead, ContactRequest $request, AddContact $action): JsonResponse
    {
        return $this->storeFor($lead, $request, $action);
    }

    public function customerIndex(Customer $customer): AnonymousResourceCollection
    {
        return $this->listFor($customer);
    }

    public function customerStore(Customer $customer, ContactRequest $request, AddContact $action): JsonResponse
    {
        return $this->storeFor($customer, $request, $action);
    }

    public function update(Contact $contact, ContactRequest $request, UpdateContact $action): ContactResource
    {
        $this->authorize('update', $this->parentOf($contact));

        return ContactResource::make($action->handle($contact, $request->toData()));
    }

    public function destroy(Contact $contact, DeleteContact $action): Response
    {
        $this->authorize('update', $this->parentOf($contact));

        $action->handle($contact);

        return response()->noContent();
    }

    /**
     * @param  Lead|Customer  $parent
     */
    private function listFor(Model $parent): AnonymousResourceCollection
    {
        $this->authorize('view', $parent);

        return ContactResource::collection(
            $parent->contacts()->orderByDesc('is_primary')->orderBy('name')->paginate(),
        );
    }

    /**
     * @param  Lead|Customer  $parent
     */
    private function storeFor(Model $parent, ContactRequest $request, AddContact $action): JsonResponse
    {
        $this->authorize('update', $parent);

        return ContactResource::make($action->handle($parent, $request->toData()))
            ->response()->setStatusCode(201);
    }

    private function parentOf(Contact $contact): Model
    {
        $parent = $contact->contactable;

        if ($parent === null) {
            abort(404);
        }

        return $parent;
    }
}
