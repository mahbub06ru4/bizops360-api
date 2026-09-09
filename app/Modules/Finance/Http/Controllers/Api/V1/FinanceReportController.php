<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Actions\BuildCustomerDues;
use App\Modules\Finance\Actions\BuildFinanceOverview;
use App\Modules\Finance\Actions\BuildMonthlyFinanceReport;
use App\Modules\Finance\Actions\BuildOutstandingInvoices;
use App\Modules\Finance\Actions\BuildProfitAndLoss;
use App\Modules\Finance\Http\Requests\FinanceReportRequest;
use App\Modules\Finance\Http\Resources\FinanceMetricResource;
use Illuminate\Http\Request;

/**
 * Read-only finance reports for the current tenant. Requires `finance.view_reports`.
 */
class FinanceReportController extends Controller
{
    public function overview(Request $request, BuildFinanceOverview $action): FinanceMetricResource
    {
        $this->authorizeReports($request);

        return FinanceMetricResource::make($action->handle());
    }

    public function profitLoss(FinanceReportRequest $request, BuildProfitAndLoss $action): FinanceMetricResource
    {
        $this->authorizeReports($request);

        return FinanceMetricResource::make($action->handle($request->from(), $request->to()));
    }

    public function outstandingInvoices(Request $request, BuildOutstandingInvoices $action): FinanceMetricResource
    {
        $this->authorizeReports($request);

        return FinanceMetricResource::make($action->handle());
    }

    public function customerDues(Request $request, BuildCustomerDues $action): FinanceMetricResource
    {
        $this->authorizeReports($request);

        return FinanceMetricResource::make($action->handle());
    }

    public function monthly(FinanceReportRequest $request, BuildMonthlyFinanceReport $action): FinanceMetricResource
    {
        $this->authorizeReports($request);

        return FinanceMetricResource::make($action->handle($request->year()));
    }

    private function authorizeReports(Request $request): void
    {
        abort_unless((bool) $request->user()?->can('finance.view_reports'), 403);
    }
}
