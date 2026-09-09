<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\VisaRequirementData;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Industry\Travel\Models\VisaRequirement;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class AddVisaRequirement
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application, VisaRequirementData $data): VisaRequirement
    {
        $this->assertTenantOwns($application);

        if ($application->stage->isClosed() || $application->stage === VisaStage::Submitted || $application->stage === VisaStage::Processing) {
            throw ValidationException::withMessages([
                'visa_application' => 'Documents can only be changed before the application is submitted.',
            ]);
        }

        /** @var VisaRequirement $requirement */
        $requirement = $application->requirements()->make($data->toAttributes());
        $requirement->tenant_id = (int) $application->tenant_id;
        $requirement->save();

        if ($application->stage === VisaStage::Draft) {
            $application->stage = VisaStage::DocumentsPending;
            $application->save();
        }

        return $requirement;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
