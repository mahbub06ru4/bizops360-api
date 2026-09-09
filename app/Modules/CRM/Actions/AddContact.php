<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\ContactData;
use App\Modules\CRM\Models\Contact;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Adds a contact to a lead or customer. Marking it primary demotes any existing
 * primary contact on the same record.
 */
class AddContact
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Model $contactable, ContactData $data): Contact
    {
        $this->assertTenantOwns($contactable);

        return DB::transaction(function () use ($contactable, $data): Contact {
            if ($data->isPrimary) {
                $this->demoteExistingPrimary($contactable);
            }

            $contact = new Contact($data->toAttributes());
            $contact->tenant_id = (int) $contactable->getAttribute('tenant_id');
            $contact->contactable_type = $contactable->getMorphClass();
            $contact->contactable_id = (int) $contactable->getKey();
            $contact->save();

            return $contact;
        });
    }

    private function demoteExistingPrimary(Model $contactable): void
    {
        Contact::query()
            ->where('contactable_type', $contactable->getMorphClass())
            ->where('contactable_id', $contactable->getKey())
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
