<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Middleware;

use App\Modules\Tenant\Context\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the authenticated user's tenant to the {@see TenantContext} for the
 * duration of the request, and points spatie/laravel-permission's team id at the
 * same tenant so every RBAC check ($user->can(...), policies) is tenant-scoped.
 *
 * Runs after authentication. If the user has no tenant the request continues
 * without a bound tenant (platform-level endpoints).
 */
class ResolveTenant
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly PermissionRegistrar $registrar,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->tenant !== null) {
            $this->context->set($user->tenant);
            $this->registrar->setPermissionsTeamId($user->tenant->getKey());
        }

        return $next($request);
    }
}
