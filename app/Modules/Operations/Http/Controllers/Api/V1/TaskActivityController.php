<?php

declare(strict_types=1);

namespace App\Modules\Operations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Operations\Http\Resources\TaskActivityResource;
use App\Modules\Operations\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskActivityController extends Controller
{
    public function index(Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return TaskActivityResource::collection(
            $task->activities()->with('causer')->latest()->paginate(),
        );
    }
}
