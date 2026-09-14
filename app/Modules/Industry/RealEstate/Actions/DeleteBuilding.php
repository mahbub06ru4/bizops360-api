<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Models\Building;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteBuilding
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Building $building): void
    {
        $this->assertTenantOwns($building);

        if ($building->units()->exists()) {
            throw ValidationException::withMessages([
                'building' => 'Remove this building\'s units before deleting it.',
            ]);
        }

        $building->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
