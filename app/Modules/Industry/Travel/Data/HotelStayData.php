<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Industry\Travel\Domain\BoardBasis;
use Illuminate\Support\Carbon;

/**
 * Application input for one hotel reservation within a booking.
 */
final readonly class HotelStayData
{
    public function __construct(
        public string $hotelName,
        public string $city,
        public ?string $country,
        public string $checkIn,
        public string $checkOut,
        public int $nights,
        public ?string $roomType,
        public int $rooms,
        public int $guests,
        public BoardBasis $boardBasis,
        public ?string $confirmationNo,
        public ?string $note,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        $checkIn = Carbon::parse((string) $row['check_in'])->startOfDay();
        $checkOut = Carbon::parse((string) $row['check_out'])->startOfDay();

        return new self(
            hotelName: (string) $row['hotel_name'],
            city: (string) $row['city'],
            country: isset($row['country']) ? (string) $row['country'] : null,
            checkIn: $checkIn->toDateString(),
            checkOut: $checkOut->toDateString(),
            nights: max(1, (int) $checkIn->diffInDays($checkOut)),
            roomType: isset($row['room_type']) ? (string) $row['room_type'] : null,
            rooms: isset($row['rooms']) ? max(1, (int) $row['rooms']) : 1,
            guests: isset($row['guests']) ? max(1, (int) $row['guests']) : 1,
            boardBasis: BoardBasis::from((string) ($row['board_basis'] ?? BoardBasis::RoomOnly->value)),
            confirmationNo: isset($row['confirmation_no']) ? (string) $row['confirmation_no'] : null,
            note: isset($row['note']) ? (string) $row['note'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'hotel_name' => $this->hotelName,
            'city' => $this->city,
            'country' => $this->country,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'nights' => $this->nights,
            'room_type' => $this->roomType,
            'rooms' => $this->rooms,
            'guests' => $this->guests,
            'board_basis' => $this->boardBasis,
            'confirmation_no' => $this->confirmationNo,
            'note' => $this->note,
        ];
    }
}
