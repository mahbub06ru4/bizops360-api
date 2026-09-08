<?php

declare(strict_types=1);

arch('no debug statements ship')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('actions are invokable use cases with a handle() method')
    ->expect('App\Modules\Identity\Actions')
    ->toHaveMethod('handle');

arch('DTOs are readonly')
    ->expect('App\Modules\Identity\Data')
    ->toBeReadonly();

arch('controllers do not use the DB facade directly')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Modules\Identity\Http\Controllers');

arch('strict types everywhere in the modules')
    ->expect('App\Modules')
    ->toUseStrictTypes();
