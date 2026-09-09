<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\CRM\Models\Customer;
use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Data\VisaApplicationData;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class UpdateVisaApplication
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaApplication $application, VisaApplicationData $data): VisaApplication
    {
        $this->assertTenantOwns($application);

        if ($application->stage->isClosed()) {
            throw ValidationException::withMessages([
                'visa_application' => "A {$application->stage->value} visa application can no longer be edited.",
            ]);
        }

        $this->assertReferenceOwned($data->customerId, Customer::class);
        $this->assertReferenceOwned($data->assignedEmployeeId, Employee::class);

        // traveller_id is fixed once the case is open
        $attributes = $data->toAttributes();
        $application->fill($attributes)->save();

        return $application->refresh()->load(['traveller', 'requirements']);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
