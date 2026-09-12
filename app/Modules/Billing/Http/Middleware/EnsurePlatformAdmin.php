<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards platform-level endpoints (spans every tenant — e.g. SaaS analytics).
 * Deliberately separate from the tenant RBAC in `Authorization\Roles`: a
 * platform admin has `tenant_id = null` and this one boolean flag, not a role
 * or permission, because "sees every tenant's numbers" isn't something any
 * tenant-scoped role should ever be grantable into by mistake.
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_platform_admin !== true) {
            abort(403, 'Platform admin access required.');
        }

        return $next($request);
    }
}
