<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\LandRecord;
use App\Modules\Industry\RealEstate\Models\LandShare;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The share structure of a land-share project — commercial data, safe to
 * show a buyer. The legal {@see LandRecord}
 * this share structure rests on is a separate, admin-only resource.
 *
 * @mixin LandShare
 */
class LandShareResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_shares' => $this->total_shares,
            'share_value' => $this->share_value,
        ];
    }
}
