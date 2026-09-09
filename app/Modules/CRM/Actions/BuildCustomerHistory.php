<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Models\Customer;
use App\Modules\Tenant\Context\TenantContext;

/**
 * Assembles a customer's full CRM history: profile, originating lead, contacts,
 * follow-ups and the activity timeline.
 */
class BuildCustomerHistory
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Customer $customer): array
    {
        $this->assertTenantOwns($customer);

        return [
            'customer' => $customer->load('owner'),
            'source_lead' => $customer->sourceLead()->with('owner')->first(),
            'contacts' => $customer->contacts()->orderByDesc('is_primary')->orderBy('name')->get(),
            'follow_ups' => $customer->followups()->with('assignedEmployee')->orderByDesc('due_at')->limit(50)->get(),
            'activities' => $customer->activities()->with('causer')->latest('id')->limit(100)->get(),
        ];
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
