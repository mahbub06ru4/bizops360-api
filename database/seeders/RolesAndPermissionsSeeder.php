<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Authorization\Actions\ProvisionTenantRbac;
use App\Modules\Authorization\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the global permission catalogue. Per-tenant roles are created by
 * {@see ProvisionTenantRbac} during tenant
 * registration.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Roles::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
