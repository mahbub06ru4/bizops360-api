<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Models\User;
use App\Modules\Industry\RealEstate\Database\Factories\ProjectDocumentFactory;
use App\Modules\Industry\RealEstate\Domain\ProjectDocumentType;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A private file attached to a {@see RealEstateProject} — RAJUK approval, land
 * deed, mutation certificate, etc. `is_private` defaults true: never embed
 * these in a public resource, only serve them through a Policy-gated,
 * signed-URL download (spec §9, roadmap §9 risk 3).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property int|null $uploaded_by
 * @property ProjectDocumentType $document_type
 * @property string $file_path
 * @property bool $is_private
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['document_type', 'is_private'])]
class ProjectDocument extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ProjectDocumentFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
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
            'document_type' => ProjectDocumentType::class,
            'is_private' => 'boolean',
        ];
    }

    protected static function newFactory(): ProjectDocumentFactory
    {
        return ProjectDocumentFactory::new();
    }
}
