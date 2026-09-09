<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class CancelVisaApplication
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application, ?string $reason = null): VisaApplication
    {
        $this->assertTenantOwns($application);

        if ($application->stage->isClosed()) {
            throw ValidationException::withMessages([
                'visa_application' => "A {$application->stage->value} visa application cannot be cancelled.",
            ]);
        }

        $application->stage = VisaStage::Cancelled;

        if ($reason !== null) {
            $application->decision_note = $reason;
        }

        $application->save();

        return $application->refresh()->load(['traveller', 'requirements']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
