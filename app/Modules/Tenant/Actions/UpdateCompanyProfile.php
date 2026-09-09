<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Actions;

use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Data\CompanyProfileData;
use App\Modules\Tenant\Models\Tenant;

/**
 * Updates the profile of the tenant bound to the current request context. The
 * slug is immutable and never taken from input.
 */
class UpdateCompanyProfile
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(CompanyProfileData $data): Tenant
    {
        $tenant = $this->context->tenant();

        $tenant->fill($data->toAttributes());
        $tenant->save();

        return $tenant->refresh();
    }
}
