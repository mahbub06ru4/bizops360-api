<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\ProjectPricingData;
use App\Modules\Industry\RealEstate\Models\ProjectPricing;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Sets (or replaces) a project's one cost breakdown row, computing
 * `estimated_total` from the three cost lines rather than trusting a
 * client-supplied total.
 */
class SetProjectPricing
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, ProjectPricingData $data): ProjectPricing
    {
        $this->assertTenantOwns($project);

        $total = Money::fromDecimal($data->landCost)
            ->add(Money::fromDecimal($data->constructionCost))
            ->add(Money::fromDecimal($data->consultancyCost));

        $pricing = $project->pricing ?? new ProjectPricing;
        $pricing->tenant_id = (int) $project->tenant_id;
        $pricing->project_id = $project->getKey();
        $pricing->land_cost = $data->landCost;
        $pricing->construction_cost = $data->constructionCost;
        $pricing->consultancy_cost = $data->consultancyCost;
        $pricing->estimated_total = $total->toDecimalString();
        $pricing->currency = $data->currency;
        $pricing->save();

        return $pricing;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
