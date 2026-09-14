<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Data\LandRecordData;
use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Policies\LandRecordPolicy;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Records the legal land reference for a `land_share`/`plot` project. Only
 * reachable by tenant admins — see {@see LandRecordPolicy}.
 */
class AddLandRecord
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(RealEstateProject $project, LandRecordData $data): LandRecord
    {
        $this->assertTenantOwns($project);

        $record = new LandRecord($data->toAttributes());
        $record->tenant_id = (int) $project->tenant_id;
        $record->project_id = $project->getKey();
        $record->save();

        return $record;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
