<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Actions\Concerns\ResolvesTenantId;
use App\Modules\Organization\Models\Department;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Project and task rollups per department for the current tenant. A department's
 * tasks are the tasks of its projects.
 */
class BuildDepartmentPerformance
{
    use ResolvesTenantId;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        $tenantId = $this->currentTenantId();
        $now = Carbon::now();

        $rows = DB::table('tasks')
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->where('tasks.tenant_id', $tenantId)
            ->whereNotNull('projects.department_id')
            ->groupBy('projects.department_id')
            ->selectRaw('projects.department_id as department_id')
            ->selectRaw('count(*) as total_tasks')
            ->selectRaw("coalesce(sum(case when tasks.status = 'done' then 1 else 0 end), 0) as completed_tasks")
            ->selectRaw("coalesce(sum(case when tasks.status not in ('done', 'cancelled') then 1 else 0 end), 0) as open_tasks")
            ->selectRaw("coalesce(sum(case when tasks.status not in ('done', 'cancelled') and tasks.due_at is not null and tasks.due_at < ? then 1 else 0 end), 0) as overdue_tasks", [$now])
            ->get();

        $departmentIds = $rows->pluck('department_id')->all();

        $departments = Department::query()
            ->whereIn('id', $departmentIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $projectCounts = DB::table('projects')
            ->where('tenant_id', $tenantId)
            ->whereIn('department_id', $departmentIds)
            ->groupBy('department_id')
            ->selectRaw('department_id, count(*) as c')
            ->pluck('c', 'department_id');

        return $rows
            ->map(function (\stdClass $row) use ($departments, $projectCounts): array {
                /** @var Department|null $department */
                $department = $departments->get($row->department_id);

                return [
                    'department_id' => (int) $row->department_id,
                    'department_name' => $department?->name,
                    'projects' => (int) $projectCounts->get($row->department_id, 0),
                    'total_tasks' => (int) $row->total_tasks,
                    'open_tasks' => (int) $row->open_tasks,
                    'completed_tasks' => (int) $row->completed_tasks,
                    'overdue_tasks' => (int) $row->overdue_tasks,
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
