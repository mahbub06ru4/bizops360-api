<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\LeadStageData;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

/**
 * Moves a lead along the pipeline. Converting is done through {@see ConvertLead},
 * not here; a converted lead is frozen.
 */
class MoveLeadStage
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Lead $lead, LeadStageData $data): Lead
    {
        $this->assertTenantOwns($lead);

        if ($lead->stage === LeadStage::Converted) {
            throw ValidationException::withMessages([
                'stage' => 'A converted lead cannot be moved.',
            ]);
        }

        $lead->stage = $data->stage;
        $lead->lost_reason = $data->stage === LeadStage::Lost ? $data->lostReason : null;
        $lead->save();

        return $lead->refresh()->load('owner');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
