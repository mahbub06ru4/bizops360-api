<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain;

/**
 * The sales-pipeline stage of a lead.
 */
enum LeadStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Interested = 'interested';
    case FollowUp = 'follow_up';
    case Negotiation = 'negotiation';
    case Converted = 'converted';
    case Lost = 'lost';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Stages a lead can still be worked in.
     */
    public function isOpen(): bool
    {
        return match ($this) {
            self::Converted, self::Lost => false,
            default => true,
        };
    }

    public function isConverted(): bool
    {
        return $this === self::Converted;
    }
}
