<?php

declare(strict_types=1);

namespace App\Modules\Audit\Policies;

use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit.view');
    }
}
