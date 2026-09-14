<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Models\Amenity;
use App\Modules\Tenant\Context\TenantContext;

class DeleteAmenity
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Amenity $amenity): void
    {
        $this->assertTenantOwns($amenity);

        $amenity->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
