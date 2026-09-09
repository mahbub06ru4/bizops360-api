<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\ContactData;
use App\Modules\CRM\Models\Contact;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

class UpdateContact
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Contact $contact, ContactData $data): Contact
    {
        $this->assertTenantOwns($contact);

        return DB::transaction(function () use ($contact, $data): Contact {
            if ($data->isPrimary) {
                Contact::query()
                    ->where('contactable_type', $contact->contactable_type)
                    ->where('contactable_id', $contact->contactable_id)
                    ->whereKeyNot($contact->getKey())
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $contact->fill($data->toAttributes())->save();

            return $contact->refresh();
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
