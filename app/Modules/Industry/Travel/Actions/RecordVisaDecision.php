<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\RecordVisaDecisionData;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Records the mission's decision — approved or rejected.
 */
class RecordVisaDecision
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application, RecordVisaDecisionData $data): VisaApplication
    {
        $this->assertTenantOwns($application);

        if (! in_array($data->outcome, [VisaStage::Approved, VisaStage::Rejected], true)) {
            throw ValidationException::withMessages(['outcome' => 'A decision must be approved or rejected.']);
        }

        if (! $application->stage->canTransitionTo($data->outcome)) {
            throw ValidationException::withMessages([
                'visa_application' => "A visa application at stage [{$application->stage->value}] cannot be decided.",
            ]);
        }

        $application->stage = $data->outcome;
        $application->decision_on = Carbon::parse($data->decisionOn);
        $application->decision_note = $data->decisionNote;
        $application->save();

        return $application->refresh()->load(['traveller', 'requirements']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
