<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Modules\Industry\Travel\Http\Middleware\EnsureIndustry;
use App\Modules\Tenant\Http\Middleware\ResolveTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (and any single-hop PaaS load balancer) sits in front of the
        // app as the only entry point, so trust its X-Forwarded-* headers —
        // otherwise HTTPS detection, client IPs, and rate limiting all see the
        // proxy instead of the real request.
        $middleware->trustProxies(at: '*');

        $middleware->prependToGroup('api', ForceJsonResponse::class);

        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'industry' => EnsureIndustry::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
