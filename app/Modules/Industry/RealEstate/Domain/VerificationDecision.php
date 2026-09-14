<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

/**
 * The platform admin's verdict on a project's admin-verification review.
 * Basic Pending/Verified/Rejected only — manual document review, no OCR
 * (roadmap Phase 1 gating note).
 */
enum VerificationDecision: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
