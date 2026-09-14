<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\LandShareData;
use App\Modules\Industry\RealEstate\Models\LandShare;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Records the share structure for a `land_share` project (how many shares,
 * value per share). The ownership ledger itself is Phase 1+.
 */
class AddLandShare
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, LandShareData $data): LandShare
    {
        $this->assertTenantOwns($project);

        $share = new LandShare($data->toAttributes());
        $share->tenant_id = (int) $project->tenant_id;
        $share->project_id = $project->getKey();
        $share->save();

        return $share;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
