<?php

declare(strict_types=1);

namespace App\Modules\HR\Policies;

use App\Models\User;
use App\Modules\HR\Models\EmployeeDocument;

class EmployeeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employee_document.view');
    }

    public function view(User $user, EmployeeDocument $document): bool
    {
        return $user->can('employee_document.view')
            && $this->sameTenant($user, $document)
            && ($user->can('employee_document.view_all') || $this->belongsToUser($user, $document));
    }

    public function create(User $user): bool
    {
        return $user->can('employee_document.upload');
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('employee_document.delete') && $this->sameTenant($user, $document);
    }

    private function belongsToUser(User $user, EmployeeDocument $document): bool
    {
        return $document->employee !== null && $document->employee->user_id === $user->getKey();
    }

    private function sameTenant(User $user, EmployeeDocument $document): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $document->tenant_id;
    }
}
