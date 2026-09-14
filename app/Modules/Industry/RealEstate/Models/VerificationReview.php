<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Models;

use App\Models\User;
use App\Modules\Industry\RealEstate\Actions\RejectProject;
use App\Modules\Industry\RealEstate\Actions\VerifyProject;
use App\Modules\Industry\RealEstate\Database\Factories\VerificationReviewFactory;
use App\Modules\Industry\RealEstate\Domain\VerificationDecision;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The platform admin's verdict on a {@see RealEstateProject}'s submission for
 * verification. Basic Pending/Verified/Rejected only, manual document review
 * — no OCR (roadmap Phase 1 gating note). Written only by
 * {@see VerifyProject} and
 * {@see RejectProject}, both gated to
 * platform admins.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $project_id
 * @property int|null $reviewed_by
 * @property VerificationDecision $decision
 * @property string|null $notes
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['project_id', 'reviewed_by', 'decision', 'notes', 'reviewed_at'])]
class VerificationReview extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<VerificationReviewFactory> */
    use HasFactory;

    /** @return BelongsTo<RealEstateProject, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'project_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decision' => VerificationDecision::class,
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): VerificationReviewFactory
    {
        return VerificationReviewFactory::new();
    }
}
