<?php

declare(strict_types=1);

namespace App\Modules\Operations\Actions;

use App\Models\User;
use App\Modules\Operations\Actions\Concerns\InteractsWithTenant;
use App\Modules\Operations\Actions\Concerns\NotifiesTaskParticipants;
use App\Modules\Operations\Data\TaskCommentData;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskComment;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Adds a comment to a task and records the matching activity entry.
 */
class AddTaskComment
{
    use InteractsWithTenant;
    use NotifiesTaskParticipants;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Task $task, TaskCommentData $data, User $author): TaskComment
    {
        $this->assertTenantOwns($task);

        return DB::transaction(function () use ($task, $data, $author): TaskComment {
            $comment = new TaskComment(['body' => $data->body]);
            $comment->tenant_id = (int) $task->tenant_id;
            $comment->task_id = (int) $task->getKey();
            $comment->author_id = $author->getKey();
            $comment->save();

            $activity = new TaskActivity([
                'event' => 'commented',
                'description' => 'Comment added',
                'properties' => ['comment_id' => $comment->getKey()],
            ]);
            $activity->tenant_id = (int) $task->tenant_id;
            $activity->task_id = (int) $task->getKey();
            $activity->causer_id = $author->getKey();
            $activity->save();

            $this->notifyTaskEvent($task, 'commented', "New comment on \"{$task->title}\".", $author);

            return $comment->load('author');
        });
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
