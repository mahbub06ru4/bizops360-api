<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\FollowUpData;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Support\RecordsCrmActivity;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Schedules a follow-up against a lead or customer and logs it on that record.
 */
class ScheduleFollowUp
{
    use InteractsWithTenant;
    use RecordsCrmActivity;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Model $followupable, FollowUpData $data, User $creator): FollowUp
    {
        $this->assertTenantOwns($followupable);
        $this->assertReferenceOwned($data->assignedEmployeeId, Employee::class);

        return DB::transaction(function () use ($followupable, $data, $creator): FollowUp {
            $followUp = new FollowUp($data->toAttributes());
            $followUp->tenant_id = (int) $followupable->getAttribute('tenant_id');
            $followUp->followupable_type = $followupable->getMorphClass();
            $followUp->followupable_id = (int) $followupable->getKey();
            $followUp->created_by = $creator->getKey();
            $followUp->save();

            $this->recordCrmActivity(
                $followupable,
                'follow_up_scheduled',
                "{$data->type->value} follow-up scheduled",
                (int) $creator->getKey(),
                ['follow_up_id' => $followUp->getKey(), 'due_at' => $followUp->due_at->toIso8601String()],
            );

            return $followUp->load('assignedEmployee');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
