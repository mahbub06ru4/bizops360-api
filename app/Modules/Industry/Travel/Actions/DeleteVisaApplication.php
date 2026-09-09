<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteVisaApplication
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application): void
    {
        $this->assertTenantOwns($application);

        if (! in_array($application->stage, [VisaStage::Draft, VisaStage::DocumentsPending, VisaStage::Cancelled], true)) {
            throw ValidationException::withMessages([
                'visa_application' => 'Only a draft or cancelled visa application can be deleted; cancel it instead.',
            ]);
        }

        $application->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
