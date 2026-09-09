<?php

declare(strict_types=1);

namespace App\Modules\Organization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a `['name' => string, 'permissions' => list<string>]` entry from the
 * role catalogue.
 */
class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{name: string, permissions: list<string>} $role */
        $role = $this->resource;

        return [
            'name' => $role['name'],
            'permissions' => $role['permissions'],
        ];
    }
}
