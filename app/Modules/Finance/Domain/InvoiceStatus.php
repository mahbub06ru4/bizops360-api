<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Void = 'void';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /**
     * Whether the invoice still contributes to receivables (money is owed).
     */
    public function isOutstanding(): bool
    {
        return match ($this) {
            self::Draft, self::Sent, self::Partial => true,
            self::Paid, self::Refunded, self::Void => false,
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft || $this === self::Sent;
    }
}
