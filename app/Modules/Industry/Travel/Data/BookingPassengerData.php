<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Finance\Domain\Money;

/**
 * Application input for one passenger line on a booking.
 */
final readonly class BookingPassengerData
{
    public function __construct(
        public int $travellerId,
        public ?string $ticketNumber,
        public ?string $baggage,
        public ?string $fareAmount,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            travellerId: (int) $row['traveller_id'],
            ticketNumber: isset($row['ticket_number']) ? (string) $row['ticket_number'] : null,
            baggage: isset($row['baggage']) ? (string) $row['baggage'] : null,
            fareAmount: isset($row['fare_amount']) ? Money::fromDecimal((string) $row['fare_amount'])->toDecimalString() : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'traveller_id' => $this->travellerId,
            'ticket_number' => $this->ticketNumber,
            'baggage' => $this->baggage,
            'fare_amount' => $this->fareAmount,
        ];
    }
}
