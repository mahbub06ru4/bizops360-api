<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Modules\Operations\Domain\ProjectStatus;
use App\Modules\Operations\Domain\TaskPriority;
use App\Modules\Operations\Domain\TaskStatus;
use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Builds the manager dashboard summary for the current tenant. Every query is
 * tenant-scoped by the models' global scope.
 */
class BuildOperationsOverview
{
    /** @var list<string> */
    private const array CLOSED = ['done', 'cancelled'];

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $now = Carbon::now();

        return [
            'tasks' => [
                'open' => Task::query()->whereNotIn('status', self::CLOSED)->count(),
                'overdue' => Task::query()->whereNotIn('status', self::CLOSED)
                    ->whereNotNull('due_at')->where('due_at', '<', $now)->count(),
                'due_today' => Task::query()->whereNotIn('status', self::CLOSED)
                    ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])->count(),
                'unassigned' => Task::query()->whereNotIn('status', self::CLOSED)
                    ->whereNull('assignee_employee_id')->whereNull('assignee_team_id')->count(),
                'completed_this_week' => Task::query()
                    ->where('status', TaskStatus::Done->value)
                    ->where('completed_at', '>=', $now->copy()->startOfWeek())->count(),
                'by_status' => $this->tally(Task::class, 'status', TaskStatus::values()),
                'by_priority' => $this->tally(Task::class, 'priority', TaskPriority::values()),
            ],
            'projects' => [
                'by_status' => $this->tally(Project::class, 'status', ProjectStatus::values()),
            ],
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $keys
     * @return array<string, int>
     */
    private function tally(string $modelClass, string $column, array $keys): array
    {
        $raw = $modelClass::query()
            ->selectRaw("{$column} as k, count(*) as c")
            ->groupBy($column)
            ->pluck('c', 'k')
            ->all();

        $out = [];
        foreach ($keys as $key) {
            $out[$key] = (int) ($raw[$key] ?? 0);
        }

        return $out;
    }
}
