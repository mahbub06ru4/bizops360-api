<?php

declare(strict_types=1);

namespace App\Modules\Operations\Models;

use App\Models\User;
use App\Modules\Operations\Database\Factories\TaskAttachmentFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A file attached to a task. The binary lives on a storage disk; this row is
 * metadata plus the pointer.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $task_id
 * @property int|null $uploaded_by
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TaskAttachment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TaskAttachmentFactory> */
    use HasFactory;

    /** @return BelongsTo<Task, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    protected static function newFactory(): TaskAttachmentFactory
    {
        return TaskAttachmentFactory::new();
    }
}
