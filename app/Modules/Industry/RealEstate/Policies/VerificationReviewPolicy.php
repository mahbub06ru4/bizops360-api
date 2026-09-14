<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Policies;

use App\Models\User;
use App\Modules\Industry\RealEstate\Models\VerificationReview;

/**
 * Verification is a platform-admin-only, cross-tenant operation gated by the
 * `platform_admin` middleware on the route itself (see
 * {@see \App\Modules\Billing\Http\Middleware\EnsurePlatformAdmin} and the
 * verify/reject routes in the RealEstate route file) — this Policy exists so
 * a `VerificationReview` behaves like every other tenant-owned model if it is
 * ever read from a tenant-scoped context (e.g. a seller viewing why their own
 * project was rejected), but it deliberately grants no tenant role
 * `verification_review.*` permission (see {@see \App\Modules\Authorization\Roles}):
 * no tenant staff should ever be grantable into approving their own
 * submissions.
 */
class VerificationReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin === true || ($user->tenant_id !== null && $user->can('real_estate_project.view'));
    }

    public function view(User $user, VerificationReview $review): bool
    {
        if ($user->is_platform_admin === true) {
            return true;
        }

        return $user->can('real_estate_project.view') && $this->sameTenant($user, $review);
    }

    private function sameTenant(User $user, VerificationReview $review): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $review->tenant_id;
    }
}
