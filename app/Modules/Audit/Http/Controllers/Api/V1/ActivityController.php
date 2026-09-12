<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Http\Resources\ActivityResource;
use App\Modules\Audit\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Activity::class);

        $query = Activity::query()->with('causer')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->string('log_name'));
        }

        if ($request->filled('subject_type') && $request->filled('subject_id')) {
            $query->where('subject_type', (string) $request->string('subject_type'))
                ->where('subject_id', $request->integer('subject_id'));
        }

        return ActivityResource::collection($query->paginate($request->integer('per_page', 25)));
    }
}
