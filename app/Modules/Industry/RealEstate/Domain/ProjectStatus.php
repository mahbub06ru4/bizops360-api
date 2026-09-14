<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Domain;

/**
 * Verification lifecycle of a project (roadmap §5, §6 Phase 0/2).
 *
 * draft → pending_verification → verified
 *                              ↘ rejected
 *
 * A project only ever shows trust badges like "RAJUK Approved" once `verified`
 * — never on a tenant's own say-so.
 */
enum ProjectStatus: string
{
    case Draft = 'draft';
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * A project can still be edited by its owner while draft or sent back for
     * rework; it is frozen once submitted for review or verified.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft || $this === self::Rejected;
    }
}
