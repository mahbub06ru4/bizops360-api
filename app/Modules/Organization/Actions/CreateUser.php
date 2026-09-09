<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Models\User;
use App\Modules\Organization\Actions\Concerns\InteractsWithTenant;
use App\Modules\Organization\Data\CreateUserData;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Adds a login user to the current tenant and assigns their initial roles.
 */
class CreateUser
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(CreateUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = new User;
            $user->tenant_id = $this->currentTenantId();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->save();

            $user->syncRoles($data->roles);

            return $user->fresh(['roles']);
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
