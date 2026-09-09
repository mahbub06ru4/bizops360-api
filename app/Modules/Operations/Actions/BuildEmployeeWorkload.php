<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\ResolvesTenantId;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Open / overdue / due-today task counts per assigned employee for the current
 * tenant, busiest first.
 */
class BuildEmployeeWorkload
{
    use ResolvesTenantId;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        $now = Carbon::now();

        $rows = DB::table('tasks')
            ->where('tenant_id', $this->currentTenantId())
            ->whereNotNull('assignee_employee_id')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->groupBy('assignee_employee_id')
            ->selectRaw('assignee_employee_id')
            ->selectRaw('count(*) as open_tasks')
            ->selectRaw('coalesce(sum(case when due_at is not null and due_at < ? then 1 else 0 end), 0) as overdue_tasks', [$now])
            ->selectRaw('coalesce(sum(case when due_at >= ? and due_at <= ? then 1 else 0 end), 0) as due_today', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->get();

        $employees = Employee::query()
            ->whereIn('id', $rows->pluck('assignee_employee_id')->all())
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        return $rows
            ->map(function (\stdClass $row) use ($employees): array {
                /** @var Employee|null $employee */
                $employee = $employees->get($row->assignee_employee_id);

                return [
                    'employee_id' => (int) $row->assignee_employee_id,
                    'employee_name' => $employee !== null
                        ? trim("{$employee->first_name} {$employee->last_name}")
                        : null,
                    'open_tasks' => (int) $row->open_tasks,
                    'overdue_tasks' => (int) $row->overdue_tasks,
                    'due_today' => (int) $row->due_today,
                ];
            })
            ->sortByDesc('open_tasks')
            ->values()
            ->all();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
