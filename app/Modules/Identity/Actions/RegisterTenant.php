<?php

declare(strict_types=1);

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use App\Modules\Identity\Data\RegisterTenantData;
use App\Modules\Tenant\Context\TenantContext;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Onboards a new tenant: creates the company, provisions its roles/permissions,
 * and creates the first user as the tenant owner.
 */
class RegisterTenant
{
    public function __construct(
        private readonly ProvisionTenantRbac $provisionRbac,
        private readonly TenantContext $context,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function handle(RegisterTenantData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $tenant = Tenant::create([
                'name' => $data->companyName,
                'slug' => $this->uniqueSlug($data->companyName),
                'industry' => $data->industry,
            ]);

            $this->provisionRbac->handle($tenant);

            $user = new User;
            $user->tenant_id = $tenant->getKey();
            $user->name = $data->ownerName;
            $user->email = $data->ownerEmail;
            $user->password = Hash::make($data->ownerPassword);
            $user->save();

            $this->context->set($tenant);
            $this->registrar->setPermissionsTeamId($tenant->getKey());
            $user->assignRole(Roles::OWNER);

            return $user->fresh(['tenant', 'roles']);
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tenant';

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (Tenant::where('slug', $slug)->exists());

        return $slug;
    }
}
