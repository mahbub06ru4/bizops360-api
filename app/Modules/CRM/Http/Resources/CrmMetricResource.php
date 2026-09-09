<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps a pre-computed CRM metrics array (a summary object, or one row of a
 * metrics collection).
 */
class CrmMetricResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        return $data;
    }
}
