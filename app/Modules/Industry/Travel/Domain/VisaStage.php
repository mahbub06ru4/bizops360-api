<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

/**
 * The visa-processing pipeline (spec §5.1): Customer → Visa Case → documents
 * required → documents collected → submitted → processing → approved / rejected.
 */
enum VisaStage: string
{
    case Draft = 'draft';
    case DocumentsPending = 'documents_pending';
    case DocumentsCollected = 'documents_collected';
    case Submitted = 'submitted';
    case Processing = 'processing';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isClosed(): bool
    {
        return match ($this) {
            self::Approved, self::Rejected, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * The stages this one may move to through the normal workflow. Cancellation
     * is allowed from any open stage and is handled by its own action.
     *
     * @return list<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Draft => [self::DocumentsPending, self::DocumentsCollected],
            self::DocumentsPending => [self::DocumentsCollected],
            self::DocumentsCollected => [self::Submitted],
            self::Submitted => [self::Processing, self::Approved, self::Rejected],
            self::Processing => [self::Approved, self::Rejected],
            self::Approved, self::Rejected, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedNext(), true);
    }
}
