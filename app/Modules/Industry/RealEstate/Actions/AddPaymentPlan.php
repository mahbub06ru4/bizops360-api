<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\PaymentPlanData;
use App\Modules\Industry\RealEstate\Models\ProjectPaymentPlan;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

class AddPaymentPlan
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, PaymentPlanData $data): ProjectPaymentPlan
    {
        $this->assertTenantOwns($project);

        $plan = new ProjectPaymentPlan($data->toAttributes());
        $plan->tenant_id = (int) $project->tenant_id;
        $plan->project_id = $project->getKey();
        $plan->save();

        return $plan;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
