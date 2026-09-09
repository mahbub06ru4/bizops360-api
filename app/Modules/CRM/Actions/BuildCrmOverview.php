<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use Illuminate\Support\Carbon;

/**
 * Tenant-wide CRM dashboard figures. Every query is tenant-scoped by the models'
 * global scope.
 */
class BuildCrmOverview
{
    /** @var list<string> */
    private const array CLOSED_STAGES = ['converted', 'lost'];

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $now = Carbon::now();

        $converted = Lead::query()->where('stage', LeadStage::Converted->value)->count();
        $lost = Lead::query()->where('stage', LeadStage::Lost->value)->count();
        $decided = $converted + $lost;

        $byStage = [];
        $raw = Lead::query()->selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage')->all();
        foreach (LeadStage::values() as $stage) {
            $byStage[$stage] = (int) ($raw[$stage] ?? 0);
        }

        return [
            'leads' => [
                'open' => Lead::query()->whereNotIn('stage', self::CLOSED_STAGES)->count(),
                'converted' => $converted,
                'lost' => $lost,
                'conversion_rate' => $decided > 0 ? round($converted / $decided * 100, 1) : null,
                'open_pipeline_value' => number_format(
                    (float) Lead::query()->whereNotIn('stage', self::CLOSED_STAGES)->sum('estimated_value'),
                    2,
                    '.',
                    '',
                ),
                'by_stage' => $byStage,
            ],
            'customers' => Customer::query()->count(),
            'follow_ups' => [
                'due_today' => FollowUp::query()->where('status', 'pending')
                    ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])->count(),
                'overdue' => FollowUp::query()->where('status', 'pending')
                    ->where('due_at', '<', $now)->count(),
            ],
            'activities_this_week' => CrmActivity::query()
                ->where('created_at', '>=', $now->copy()->startOfWeek())->count(),
        ];
    }
}
