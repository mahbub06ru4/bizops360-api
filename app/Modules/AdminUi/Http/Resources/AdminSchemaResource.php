<?php

declare(strict_types=1);

namespace App\Modules\AdminUi\Http\Resources;

use App\Modules\AdminUi\Domain\AdminSchemaRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps the {@see AdminSchemaRegistry} array
 * output — a descriptive payload, not an Eloquent-backed resource.
 */
class AdminSchemaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $schema */
        $schema = $this->resource;

        return $schema;
    }
}
