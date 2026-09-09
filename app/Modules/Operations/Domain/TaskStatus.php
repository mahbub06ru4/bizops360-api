<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Blocked = 'blocked';
    case Done = 'done';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function isComplete(): bool
    {
        return $this === self::Done;
    }

    public function isOpen(): bool
    {
        return match ($this) {
            self::Done, self::Cancelled => false,
            default => true,
        };
    }
}
