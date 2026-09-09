<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Actions\DeleteIncome;
use App\Modules\Finance\Actions\RecordIncome;
use App\Modules\Finance\Actions\UpdateIncome;
use App\Modules\Finance\Http\Requests\IncomeRequest;
use App\Modules\Finance\Http\Resources\IncomeResource;
use App\Modules\Finance\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class IncomeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Income::class);

        $query = Income::query()->with('customer')->latest('received_on')->latest('id');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        return IncomeResource::collection($query->paginate());
    }

    public function store(IncomeRequest $request, RecordIncome $action): JsonResponse
    {
        $this->authorize('create', Income::class);

        /** @var User $user */
        $user = $request->user();

        return IncomeResource::make($action->handle($request->toData(), $user))
            ->response()->setStatusCode(201);
    }

    public function show(Income $income): IncomeResource
    {
        $this->authorize('view', $income);

        return IncomeResource::make($income->load('customer'));
    }

    public function update(IncomeRequest $request, Income $income, UpdateIncome $action): IncomeResource
    {
        $this->authorize('update', $income);

        return IncomeResource::make($action->handle($income, $request->toData()));
    }

    public function destroy(Income $income, DeleteIncome $action): Response
    {
        $this->authorize('delete', $income);

        $action->handle($income);

        return response()->noContent();
    }
}
