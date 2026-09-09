<?php

declare(strict_types=1);

arch('no debug statements ship')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('actions are invokable use cases with a handle() method')
    ->expect('App\Modules\Identity\Actions')
    ->toHaveMethod('handle');

arch('organization actions are use cases with a handle() method')
    ->expect('App\Modules\Organization\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\Modules\Organization\Actions\Concerns');

arch('DTOs are readonly')
    ->expect('App\Modules\Identity\Data')
    ->toBeReadonly();

arch('organization DTOs are readonly')
    ->expect('App\Modules\Organization\Data')
    ->toBeReadonly();

arch('tenant actions are use cases with a handle() method')
    ->expect('App\Modules\Tenant\Actions')
    ->toHaveMethod('handle');

arch('tenant DTOs are readonly')
    ->expect('App\Modules\Tenant\Data')
    ->toBeReadonly();

arch('hr actions are use cases with a handle() method')
    ->expect('App\Modules\HR\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\Modules\HR\Actions\Concerns');

arch('hr DTOs are readonly')
    ->expect('App\Modules\HR\Data')
    ->toBeReadonly();

arch('operations actions are use cases with a handle() method')
    ->expect('App\Modules\Operations\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\Modules\Operations\Actions\Concerns');

arch('operations DTOs are readonly')
    ->expect('App\Modules\Operations\Data')
    ->toBeReadonly();

arch('crm actions are use cases with a handle() method')
    ->expect('App\Modules\CRM\Actions')
    ->toHaveMethod('handle')
    ->ignoring('App\Modules\CRM\Actions\Concerns');

arch('crm DTOs are readonly')
    ->expect('App\Modules\CRM\Data')
    ->toBeReadonly();

arch('controllers do not use the DB facade directly')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Modules\Identity\Http\Controllers')
    ->not->toBeUsedIn('App\Modules\Organization\Http\Controllers')
    ->not->toBeUsedIn('App\Modules\Tenant\Http\Controllers')
    ->not->toBeUsedIn('App\Modules\HR\Http\Controllers')
    ->not->toBeUsedIn('App\Modules\Operations\Http\Controllers')
    ->not->toBeUsedIn('App\Modules\CRM\Http\Controllers');

arch('strict types everywhere in the modules')
    ->expect('App\Modules')
    ->toUseStrictTypes();
