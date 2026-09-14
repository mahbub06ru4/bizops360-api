<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\ProjectDocument;

/**
 * Project documents are private files (RAJUK approval, land deed, …) —
 * tenant staff with `project_document.view` only, never a public/buyer
 * audience, and never embedded in {@see \App\Modules\Industry\RealEstate\Http\Resources\ProjectResource}.
 */
class ProjectDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('project_document.view');
    }

    public function view(User $user, ProjectDocument $document): bool
    {
        return $user->can('project_document.view') && $this->sameTenant($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->can('project_document.upload');
    }

    private function sameTenant(User $user, ProjectDocument $document): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $document->tenant_id;
    }
}
