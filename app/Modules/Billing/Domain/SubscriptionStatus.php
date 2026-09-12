<?php

declare(strict_types=1);

namespace App\Modules\Billing\Domain;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';

    public function isUsable(): bool
    {
        return $this === self::Trialing || $this === self::Active;
    }
}
