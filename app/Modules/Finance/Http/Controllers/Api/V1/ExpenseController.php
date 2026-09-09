<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Actions\DeleteExpense;
use App\Modules\Finance\Actions\RecordExpense;
use App\Modules\Finance\Actions\UpdateExpense;
use App\Modules\Finance\Http\Requests\ExpenseRequest;
use App\Modules\Finance\Http\Resources\ExpenseResource;
use App\Modules\Finance\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::query()->with('employee')->latest('spent_on')->latest('id');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        return ExpenseResource::collection($query->paginate());
    }

    public function store(ExpenseRequest $request, RecordExpense $action): JsonResponse
    {
        $this->authorize('create', Expense::class);

        /** @var User $user */
        $user = $request->user();

        return ExpenseResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Expense $expense): ExpenseResource
    {
        $this->authorize('view', $expense);

        return ExpenseResource::make($expense->load('employee'));
    }

    public function update(ExpenseRequest $request, Expense $expense, UpdateExpense $action): ExpenseResource
    {
        $this->authorize('update', $expense);

        return ExpenseResource::make($action->handle($expense, $request->toData()));
    }

    public function destroy(Expense $expense, DeleteExpense $action): Response
    {
        $this->authorize('delete', $expense);

        $action->handle($expense);

        return response()->noContent();
    }
}
