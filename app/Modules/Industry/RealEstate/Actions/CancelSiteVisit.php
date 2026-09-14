<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\SiteVisitStatus;
use App\Modules\Industry\RealEstate\Models\SiteVisit;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class CancelSiteVisit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(SiteVisit $visit): SiteVisit
    {
        $this->assertTenantOwns($visit);

        if (! $visit->status->isOpen()) {
            throw ValidationException::withMessages([
                'visit' => "A {$visit->status->value} visit cannot be cancelled.",
            ]);
        }

        $visit->status = SiteVisitStatus::Cancelled;
        $visit->save();

        return $visit->refresh();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
