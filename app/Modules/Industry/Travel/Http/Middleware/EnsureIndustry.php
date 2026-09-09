<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Middleware;

use App\Modules\Tenant\Context\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates an industry-specific module: the route group is only reachable when the
 * current tenant's `industry` matches. Runs after `tenant`, so the context is
 * already bound.
 */
class EnsureIndustry
{
    public function __construct(private readonly TenantContext $context) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $industry): Response
    {
        abort_unless(
            $this->context->has() && $this->context->tenant()->industry === $industry,
            403,
            "This module is only available to {$industry} tenants.",
        );

        return $next($request);
    }
}
