<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Domain\FollowUpStatus;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class CancelFollowUp
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(FollowUp $followUp): FollowUp
    {
        $this->assertTenantOwns($followUp);

        if (! $followUp->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This follow-up is already resolved.',
            ]);
        }

        $followUp->status = FollowUpStatus::Cancelled;
        $followUp->save();

        return $followUp->refresh()->load('assignedEmployee');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
