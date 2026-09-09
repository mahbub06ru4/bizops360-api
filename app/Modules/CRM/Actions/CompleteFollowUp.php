<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\FollowUpOutcomeData;
use App\Modules\CRM\Domain\FollowUpStatus;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Support\RecordsCrmActivity;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteFollowUp
{
    use InteractsWithTenant;
    use RecordsCrmActivity;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(FollowUp $followUp, FollowUpOutcomeData $data, User $actor): FollowUp
    {
        $this->assertTenantOwns($followUp);

        if (! $followUp->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This follow-up is already resolved.',
            ]);
        }

        return DB::transaction(function () use ($followUp, $data, $actor): FollowUp {
            $followUp->status = FollowUpStatus::Completed;
            $followUp->outcome = $data->outcome;
            $followUp->completed_at = Carbon::now();
            $followUp->save();

            $followUp->loadMissing('followupable');

            if ($followUp->followupable !== null) {
                $this->recordCrmActivity(
                    $followUp->followupable,
                    'follow_up_completed',
                    "{$followUp->type->value} follow-up completed",
                    (int) $actor->getKey(),
                    ['follow_up_id' => $followUp->getKey(), 'outcome' => $data->outcome],
                );
            }

            return $followUp->load('assignedEmployee');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
