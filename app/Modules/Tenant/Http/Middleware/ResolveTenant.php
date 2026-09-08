<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Middleware;

use App\Modules\Tenant\Context\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the authenticated user's tenant to the {@see TenantContext} for the
 * duration of the request. Runs after authentication. If the user has no tenant
 * the request continues without a bound tenant (platform-level endpoints).
 */
class ResolveTenant
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->tenant !== null) {
            $this->context->set($user->tenant);
        }

        return $next($request);
    }
}
