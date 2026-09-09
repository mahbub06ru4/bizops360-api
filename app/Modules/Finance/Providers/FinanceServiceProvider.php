<?php

declare(strict_types=1);

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Models\Expense;
use App\Modules\Finance\Models\Income;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Policies\ExpensePolicy;
use App\Modules\Finance\Policies\IncomePolicy;
use App\Modules\Finance\Policies\InvoicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Income::class, IncomePolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}
