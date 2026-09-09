<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Actions\UpdateCompanyProfile;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Http\Requests\UpdateCompanyRequest;
use App\Modules\Tenant\Http\Resources\CompanyResource;

class CompanyController extends Controller
{
    public function __construct(private readonly TenantContext $context) {}

    /**
     * Show the current tenant's company profile.
     */
    public function show(): CompanyResource
    {
        $tenant = $this->context->tenant();
        $this->authorize('view', $tenant);

        return CompanyResource::make($tenant);
    }

    /**
     * Update the current tenant's company profile.
     */
    public function update(UpdateCompanyRequest $request, UpdateCompanyProfile $action): CompanyResource
    {
        $this->authorize('update', $this->context->tenant());

        return CompanyResource::make($action->handle($request->toData()));
    }
}
