<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Http\Resources;

use App\Modules\Industry\RealEstate\Models\LandRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Legal/private land data. Only ever served behind {@see \App\Modules\Industry\RealEstate\Policies\LandRecordPolicy} —
 * never embedded in {@see ProjectResource}.
 *
 * @mixin LandRecord
 */
class LandRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'mouza' => $this->mouza,
            'jl_no' => $this->jl_no,
            'khatian_no' => $this->khatian_no,
            'dag_no' => $this->dag_no,
        ];
    }
}
