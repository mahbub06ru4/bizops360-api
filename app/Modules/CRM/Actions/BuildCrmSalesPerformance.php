<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\ResolvesTenantId;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Per-owner-employee sales figures for the current tenant: lead outcomes,
 * converted value, win rate, customers, and open follow-ups.
 */
class BuildCrmSalesPerformance
{
    use ResolvesTenantId;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        $tenantId = $this->currentTenantId();

        /** @var array<int, array<string, int|float>> $rows */
        $rows = [];

        foreach (DB::table('leads')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('owner_employee_id')
            ->groupBy('owner_employee_id')
            ->selectRaw('owner_employee_id')
            ->selectRaw("sum(case when stage not in ('converted', 'lost') then 1 else 0 end) as open_leads")
            ->selectRaw("sum(case when stage = 'converted' then 1 else 0 end) as converted_leads")
            ->selectRaw("sum(case when stage = 'lost' then 1 else 0 end) as lost_leads")
            ->selectRaw("coalesce(sum(case when stage = 'converted' then estimated_value else 0 end), 0) as converted_value")
            ->get() as $row) {
            $rows[(int) $row->owner_employee_id] = [
                'open_leads' => (int) $row->open_leads,
                'converted_leads' => (int) $row->converted_leads,
                'lost_leads' => (int) $row->lost_leads,
                'converted_value' => (float) $row->converted_value,
                'customers' => 0,
                'open_follow_ups' => 0,
            ];
        }

        foreach (DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('owner_employee_id')
            ->groupBy('owner_employee_id')
            ->selectRaw('owner_employee_id, count(*) as c')
            ->get() as $row) {
            $id = (int) $row->owner_employee_id;
            $rows[$id] ??= $this->emptyRow();
            $rows[$id]['customers'] = (int) $row->c;
        }

        foreach (DB::table('follow_ups')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->whereNotNull('assigned_employee_id')
            ->groupBy('assigned_employee_id')
            ->selectRaw('assigned_employee_id, count(*) as c')
            ->get() as $row) {
            $id = (int) $row->assigned_employee_id;
            $rows[$id] ??= $this->emptyRow();
            $rows[$id]['open_follow_ups'] = (int) $row->c;
        }

        $names = Employee::query()
            ->whereIn('id', array_keys($rows))
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        $out = [];
        foreach ($rows as $employeeId => $data) {
            /** @var Employee|null $employee */
            $employee = $names->get($employeeId);
            $decided = $data['converted_leads'] + $data['lost_leads'];

            $out[] = [
                'employee_id' => $employeeId,
                'employee_name' => $employee !== null ? trim("{$employee->first_name} {$employee->last_name}") : null,
                'open_leads' => $data['open_leads'],
                'converted_leads' => $data['converted_leads'],
                'lost_leads' => $data['lost_leads'],
                'win_rate' => $decided > 0 ? round($data['converted_leads'] / $decided * 100, 1) : null,
                'converted_value' => number_format($data['converted_value'], 2, '.', ''),
                'customers' => $data['customers'],
                'open_follow_ups' => $data['open_follow_ups'],
            ];
        }

        usort($out, static fn (array $a, array $b): int => $b['converted_leads'] <=> $a['converted_leads']);

        return $out;
    }

    /**
     * @return array<string, int|float>
     */
    private function emptyRow(): array
    {
        return [
            'open_leads' => 0,
            'converted_leads' => 0,
            'lost_leads' => 0,
            'converted_value' => 0.0,
            'customers' => 0,
            'open_follow_ups' => 0,
        ];
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
