<?php

declare(strict_types=1);
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Tenant\Providers\TenantServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    TenantServiceProvider::class,
    IdentityServiceProvider::class,
];
