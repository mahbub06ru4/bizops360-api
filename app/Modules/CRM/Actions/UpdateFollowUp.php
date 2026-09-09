<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\FollowUpData;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class UpdateFollowUp
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(FollowUp $followUp, FollowUpData $data): FollowUp
    {
        $this->assertTenantOwns($followUp);
        $this->assertReferenceOwned($data->assignedEmployeeId, Employee::class);

        if (! $followUp->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only a pending follow-up can be edited.',
            ]);
        }

        $followUp->fill($data->toAttributes());
        $followUp->reminder_sent_at = null;
        $followUp->save();

        return $followUp->refresh()->load('assignedEmployee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
