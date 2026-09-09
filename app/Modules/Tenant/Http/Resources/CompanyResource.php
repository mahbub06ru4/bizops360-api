<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Http\Resources;

use App\Modules\Tenant\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class CompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'slug' => $this->slug,
            'industry' => $this->industry,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
        ];
    }
}
