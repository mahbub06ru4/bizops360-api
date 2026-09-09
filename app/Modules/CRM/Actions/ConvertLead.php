<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\Lead;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Converts a lead into a customer: creates the customer (from the lead, with
 * optional overrides), then marks the lead converted and links the two.
 */
class ConvertLead
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function handle(Lead $lead, array $overrides, User $actor): Customer
    {
        $this->assertTenantOwns($lead);

        if ($lead->converted_at !== null) {
            throw ValidationException::withMessages(['lead' => 'This lead has already been converted.']);
        }

        $ownerId = isset($overrides['owner_employee_id'])
            ? (int) $overrides['owner_employee_id']
            : $lead->owner_employee_id;

        $this->assertReferenceOwned($ownerId, Employee::class);

        return DB::transaction(function () use ($lead, $overrides, $actor, $ownerId): Customer {
            $customer = new Customer([
                'owner_employee_id' => $ownerId,
                'name' => $overrides['name'] ?? $lead->name,
                'type' => $overrides['type'] ?? 'business',
                'company' => $overrides['company'] ?? $lead->company,
                'email' => $overrides['email'] ?? $lead->email,
                'phone' => $overrides['phone'] ?? $lead->phone,
                'address' => $overrides['address'] ?? null,
            ]);
            $customer->tenant_id = (int) $lead->tenant_id;
            $customer->created_by = $actor->getKey();
            $customer->save();

            $lead->stage = LeadStage::Converted;
            $lead->lost_reason = null;
            $lead->converted_at = Carbon::now();
            $lead->converted_customer_id = (int) $customer->getKey();
            $lead->save();

            return $customer->load('owner');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
