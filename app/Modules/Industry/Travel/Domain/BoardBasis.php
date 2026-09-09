<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Domain;

enum BoardBasis: string
{
    case RoomOnly = 'room_only';
    case Breakfast = 'breakfast';
    case HalfBoard = 'half_board';
    case FullBoard = 'full_board';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
