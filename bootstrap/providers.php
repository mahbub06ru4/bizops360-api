<?php

declare(strict_types=1);
use App\Modules\CRM\Providers\CRMServiceProvider;
use App\Modules\Finance\Providers\FinanceServiceProvider;
use App\Modules\HR\Providers\HRServiceProvider;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Industry\Travel\Providers\TravelServiceProvider;
use App\Modules\Notifications\Providers\NotificationsServiceProvider;
use App\Modules\Operations\Providers\OperationsServiceProvider;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use App\Modules\Tenant\Providers\TenantServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    TenantServiceProvider::class,
    IdentityServiceProvider::class,
    OrganizationServiceProvider::class,
    HRServiceProvider::class,
    OperationsServiceProvider::class,
    NotificationsServiceProvider::class,
    CRMServiceProvider::class,
    FinanceServiceProvider::class,
    TravelServiceProvider::class,
];
