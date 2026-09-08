<?php

declare(strict_types=1);

namespace App\Modules\Authorization\Actions;

use App\Modules\Authorization\Roles;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the standard role set for a tenant (spatie teams mode, team_id = tenant
 * id) and grants each role its permissions. Idempotent.
 */
class ProvisionTenantRbac
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    public function handle(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant): void {
            foreach (Roles::permissions() as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            $previousTeam = $this->registrar->getPermissionsTeamId();
            $this->registrar->setPermissionsTeamId($tenant->getKey());

            try {
                foreach (Roles::all() as $roleName) {
                    $role = Role::findOrCreate($roleName, 'web');
                    $role->syncPermissions(Roles::permissionsFor($roleName));
                }
            } finally {
                $this->registrar->setPermissionsTeamId($previousTeam);
            }
        });
    }
}
