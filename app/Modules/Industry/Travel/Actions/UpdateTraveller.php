<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\TravellerData;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Context\TenantContext;

class UpdateTraveller
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Traveller $traveller, TravellerData $data): Traveller
    {
        $this->assertTenantOwns($traveller);
        $this->assertReferenceOwned($data->customerId, Customer::class);

        $traveller->fill($data->toAttributes())->save();

        return $traveller->refresh()->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
