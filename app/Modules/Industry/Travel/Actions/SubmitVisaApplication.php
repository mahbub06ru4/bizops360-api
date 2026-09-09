<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Lodges the application with the mission. Requires every mandatory document to
 * be collected first.
 */
class SubmitVisaApplication
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application, ?string $submittedOn = null, ?string $applicationNo = null): VisaApplication
    {
        $this->assertTenantOwns($application);

        if (! in_array($application->stage, [VisaStage::DocumentsCollected, VisaStage::DocumentsPending], true)) {
            throw ValidationException::withMessages([
                'visa_application' => "A visa application at stage [{$application->stage->value}] cannot be submitted.",
            ]);
        }

        $mandatoryOutstanding = $application->requirements()
            ->where('is_mandatory', true)
            ->where('collected', false)
            ->exists();

        if ($mandatoryOutstanding) {
            throw ValidationException::withMessages([
                'visa_application' => 'Collect every mandatory document before submitting.',
            ]);
        }

        $application->stage = VisaStage::Submitted;
        $application->submitted_on = Carbon::parse($submittedOn ?? Carbon::now()->toDateString());

        if ($applicationNo !== null) {
            $application->application_no = $applicationNo;
        }

        $application->save();

        return $application->refresh()->load(['traveller', 'requirements']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
