<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\VisaApplicationData;
use App\Modules\Industry\Travel\Data\VisaRequirementData;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

class OpenVisaApplication
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @param  list<VisaRequirementData>  $requirements
     */
    public function handle(VisaApplicationData $data, User $creator, array $requirements = []): VisaApplication
    {
        $traveller = $this->assertOwnedModel($data->travellerId, Traveller::class);
        $this->assertReferenceOwned($data->customerId, Customer::class);
        $this->assertReferenceOwned($data->assignedEmployeeId, Employee::class);

        $tenantId = $this->currentTenantId();

        return DB::transaction(function () use ($data, $creator, $requirements, $tenantId, $traveller): VisaApplication {
            $application = new VisaApplication($data->toAttributes());
            $application->tenant_id = $tenantId;
            $application->traveller_id = (int) $traveller->getKey();
            $application->created_by = $creator->getKey();
            $application->stage = $requirements === [] ? VisaStage::Draft : VisaStage::DocumentsPending;
            $application->save();

            foreach ($requirements as $requirement) {
                $row = $application->requirements()->make($requirement->toAttributes());
                $row->tenant_id = $tenantId;
                $row->save();
            }

            return $application->load(['traveller', 'requirements']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
