<?php

declare(strict_types=1);

namespace App\Modules\Operations\Models;

use App\Models\User;
use App\Modules\Operations\Database\Factories\TaskCommentFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A comment left on a task.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $task_id
 * @property int|null $author_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['body'])]
class TaskComment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TaskCommentFactory> */
    use HasFactory;

    /** @return BelongsTo<Task, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function newFactory(): TaskCommentFactory
    {
        return TaskCommentFactory::new();
    }
}
