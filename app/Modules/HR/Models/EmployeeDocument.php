<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Models\User;
use App\Modules\HR\Database\Factories\EmployeeDocumentFactory;
use App\Modules\HR\Domain\EmployeeDocumentCategory;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A file attached to an employee (ID, contract, certificate, …). The binary lives
 * on a storage disk; this row is metadata + the pointer.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $employee_id
 * @property EmployeeDocumentCategory $category
 * @property string $title
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property Carbon|null $expires_at
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category', 'title', 'expires_at'])]
class EmployeeDocument extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<EmployeeDocumentFactory> */
    use HasFactory;

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => EmployeeDocumentCategory::class,
            'expires_at' => 'date',
            'size' => 'integer',
        ];
    }

    protected static function newFactory(): EmployeeDocumentFactory
    {
        return EmployeeDocumentFactory::new();
    }
}
