<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\TravellerData;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Context\TenantContext;

class RegisterTraveller
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(TravellerData $data, User $creator): Traveller
    {
        $this->assertReferenceOwned($data->customerId, Customer::class);

        $traveller = new Traveller($data->toAttributes());
        $traveller->tenant_id = $this->currentTenantId();
        $traveller->created_by = $creator->getKey();
        $traveller->save();

        return $traveller->load('customer');
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
