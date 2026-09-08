<?php

declare(strict_types=1);

namespace App\Modules\Identity\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->all()),
            'permissions' => $this->when(
                $request->user()?->is($this->resource) ?? false,
                fn () => $this->getAllPermissions()->pluck('name')->all(),
            ),
        ];
    }
}
