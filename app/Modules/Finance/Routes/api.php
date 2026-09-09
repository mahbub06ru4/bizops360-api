<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Finance\Http\Controllers\Api\V1\ExpenseController;
use App\Modules\Finance\Http\Controllers\Api\V1\FinanceReportController;
use App\Modules\Finance\Http\Controllers\Api\V1\IncomeController;
use App\Modules\Finance\Http\Controllers\Api\V1\InvoiceController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware([
        ForceJsonResponse::class,
        'auth:sanctum',
        'tenant',
        'throttle:60,1',
        SubstituteBindings::class,
    ])
    ->group(function (): void {
        Route::get('finance/overview', [FinanceReportController::class, 'overview'])->name('finance.overview');
        Route::get('finance/profit-loss', [FinanceReportController::class, 'profitLoss'])->name('finance.profit-loss');
        Route::get('finance/outstanding-invoices', [FinanceReportController::class, 'outstandingInvoices'])->name('finance.outstanding-invoices');
        Route::get('finance/customer-dues', [FinanceReportController::class, 'customerDues'])->name('finance.customer-dues');
        Route::get('finance/monthly', [FinanceReportController::class, 'monthly'])->name('finance.monthly');

        Route::apiResource('incomes', IncomeController::class);
        Route::apiResource('expenses', ExpenseController::class);

        Route::get('invoices/{invoice}/payments', [InvoiceController::class, 'payments'])->name('invoices.payments.index');
        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::post('invoices/{invoice}/refunds', [InvoiceController::class, 'refund'])->name('invoices.refunds.store');
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
        Route::apiResource('invoices', InvoiceController::class);
    });
