<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class MoveVisaToProcessing
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application): VisaApplication
    {
        $this->assertTenantOwns($application);

        if (! $application->stage->canTransitionTo(VisaStage::Processing)) {
            throw ValidationException::withMessages([
                'visa_application' => "A visa application at stage [{$application->stage->value}] cannot move to processing.",
            ]);
        }

        $application->stage = VisaStage::Processing;
        $application->save();

        return $application->refresh()->load(['traveller', 'requirements']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
