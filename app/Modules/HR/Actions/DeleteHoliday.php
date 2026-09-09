<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions;

use App\Modules\HR\Actions\Concerns\InteractsWithTenant;
use App\Modules\HR\Models\Holiday;
use App\Modules\Tenant\Context\TenantContext;

class DeleteHoliday
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Holiday $holiday): void
    {
        $this->assertTenantOwns($holiday);

        $holiday->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
