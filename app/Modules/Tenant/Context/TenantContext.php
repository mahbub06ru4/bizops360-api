<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Context;

use App\Modules\Tenant\Models\Tenant;

/**
 * Holds the tenant the current request/job is acting on.
 *
 * Set once per request by the ResolveTenant middleware (or explicitly in console
 * commands, jobs and tests). Actions read it to scope work and to reject any
 * cross-tenant reference.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new \RuntimeException('No tenant is bound to the current context.');
        }

        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->getKey();
    }
}
