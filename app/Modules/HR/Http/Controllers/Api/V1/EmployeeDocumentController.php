<?php

declare(strict_types=1);

namespace App\Modules\HR\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Actions\DeleteEmployeeDocument;
use App\Modules\HR\Actions\UploadEmployeeDocument;
use App\Modules\HR\Http\Requests\StoreEmployeeDocumentRequest;
use App\Modules\HR\Http\Resources\EmployeeDocumentResource;
use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

class EmployeeDocumentController extends Controller
{
    /**
     * List employee documents. Users without `employee_document.view_all` see only
     * those attached to their own employee record.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        /** @var User $user */
        $user = $request->user();

        $query = EmployeeDocument::query()->with('employee')->latest();

        if ($user->can('employee_document.view_all')) {
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->integer('employee_id'));
            }
        } else {
            $query->where('employee_id', Employee::query()->where('user_id', $user->getKey())->value('id'));
        }

        return EmployeeDocumentResource::collection($query->paginate());
    }

    /**
     * Upload a document for an employee (multipart: file + metadata).
     */
    public function store(StoreEmployeeDocumentRequest $request, UploadEmployeeDocument $action): JsonResponse
    {
        $this->authorize('create', EmployeeDocument::class);

        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return EmployeeDocumentResource::make($action->handle($request->toData(), $file, $user))
            ->response()->setStatusCode(201);
    }

    public function show(EmployeeDocument $employeeDocument): EmployeeDocumentResource
    {
        $this->authorize('view', $employeeDocument);

        return EmployeeDocumentResource::make($employeeDocument->load('employee'));
    }

    public function destroy(EmployeeDocument $employeeDocument, DeleteEmployeeDocument $action): Response
    {
        $this->authorize('delete', $employeeDocument);

        $action->handle($employeeDocument);

        return response()->noContent();
    }
}
