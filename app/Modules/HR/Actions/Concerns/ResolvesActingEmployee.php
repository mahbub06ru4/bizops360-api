<?php

declare(strict_types=1);

namespace App\Modules\HR\Actions\Concerns;

use App\Models\User;
use App\Modules\Organization\Models\Employee;
use Illuminate\Validation\ValidationException;

/**
 * Resolves the {@see Employee} record linked to a user, for self-service actions
 * like attendance check-in.
 */
trait ResolvesActingEmployee
{
    protected function employeeForUser(User $user): Employee
    {
        $employee = Employee::query()->where('user_id', $user->getKey())->first();

        if ($employee === null) {
            throw ValidationException::withMessages([
                'employee' => 'Your account is not linked to an employee record.',
            ]);
        }

        return $employee;
    }
}
