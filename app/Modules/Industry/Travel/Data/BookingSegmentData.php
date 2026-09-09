<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

/**
 * Application input for one flight leg on a booking.
 */
final readonly class BookingSegmentData
{
    public function __construct(
        public int $sequence,
        public string $flightNumber,
        public ?string $airline,
        public string $fromAirport,
        public string $toAirport,
        public string $departAt,
        public ?string $arriveAt,
        public ?string $cabin,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row, int $sequence): self
    {
        return new self(
            sequence: isset($row['sequence']) ? (int) $row['sequence'] : $sequence,
            flightNumber: (string) $row['flight_number'],
            airline: isset($row['airline']) ? (string) $row['airline'] : null,
            fromAirport: (string) $row['from_airport'],
            toAirport: (string) $row['to_airport'],
            departAt: (string) $row['depart_at'],
            arriveAt: isset($row['arrive_at']) ? (string) $row['arrive_at'] : null,
            cabin: isset($row['cabin']) ? (string) $row['cabin'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'sequence' => $this->sequence,
            'flight_number' => $this->flightNumber,
            'airline' => $this->airline,
            'from_airport' => $this->fromAirport,
            'to_airport' => $this->toAirport,
            'depart_at' => $this->departAt,
            'arrive_at' => $this->arriveAt,
            'cabin' => $this->cabin,
        ];
    }
}
