<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Models\Contact;
use App\Modules\Tenant\Context\TenantContext;

class DeleteContact
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Contact $contact): void
    {
        $this->assertTenantOwns($contact);

        $contact->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
