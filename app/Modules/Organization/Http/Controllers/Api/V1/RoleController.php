<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Authorization\Roles;
use App\Modules\Organization\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    /**
     * The role catalogue every tenant is provisioned with, and the permissions
     * each role holds.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless((bool) $request->user()?->can('role.view'), 403);

        $roles = array_map(
            static fn (string $name): array => [
                'name' => $name,
                'permissions' => Roles::permissionsFor($name),
            ],
            Roles::all(),
        );

        return RoleResource::collection($roles);
    }
}
