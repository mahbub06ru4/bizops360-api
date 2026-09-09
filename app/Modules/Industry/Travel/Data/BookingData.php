<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Finance\Domain\Money;
use App\Modules\Industry\Travel\Domain\BookingType;

/**
 * Application input for creating or updating a Booking, with its nested
 * passenger, flight-segment, hotel-stay and itinerary lines.
 */
final readonly class BookingData
{
    /**
     * @param  list<BookingPassengerData>  $passengers
     * @param  list<BookingSegmentData>  $segments
     * @param  list<HotelStayData>  $hotelStays
     * @param  list<ItineraryItemData>  $itinerary
     */
    public function __construct(
        public ?int $customerId,
        public ?int $handledByEmployeeId,
        public BookingType $type,
        public string $title,
        public ?string $supplierName,
        public ?string $pnr,
        public ?string $airline,
        public ?string $origin,
        public ?string $destination,
        public ?string $departOn,
        public ?string $returnOn,
        public string $costAmount,
        public string $sellAmount,
        public string $commissionAmount,
        public string $currency,
        public ?string $notes,
        public array $passengers,
        public array $segments,
        public array $hotelStays,
        public array $itinerary,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        /** @var array<int, array<string, mixed>> $passengers */
        $passengers = array_values($validated['passengers'] ?? []);
        /** @var array<int, array<string, mixed>> $segments */
        $segments = array_values($validated['segments'] ?? []);
        /** @var array<int, array<string, mixed>> $hotelStays */
        $hotelStays = array_values($validated['hotel_stays'] ?? []);
        /** @var array<int, array<string, mixed>> $itinerary */
        $itinerary = array_values($validated['itinerary'] ?? []);

        $passengerData = [];
        foreach ($passengers as $row) {
            $passengerData[] = BookingPassengerData::fromArray($row);
        }

        $segmentData = [];
        foreach ($segments as $i => $row) {
            $segmentData[] = BookingSegmentData::fromArray($row, $i + 1);
        }

        $hotelData = [];
        foreach ($hotelStays as $row) {
            $hotelData[] = HotelStayData::fromArray($row);
        }

        $itineraryData = [];
        foreach ($itinerary as $i => $row) {
            $itineraryData[] = ItineraryItemData::fromArray($row, $i + 1);
        }

        return new self(
            customerId: isset($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            handledByEmployeeId: isset($validated['handled_by_employee_id']) ? (int) $validated['handled_by_employee_id'] : null,
            type: BookingType::from((string) ($validated['type'] ?? BookingType::AirTicket->value)),
            title: (string) $validated['title'],
            supplierName: isset($validated['supplier_name']) ? (string) $validated['supplier_name'] : null,
            pnr: isset($validated['pnr']) ? (string) $validated['pnr'] : null,
            airline: isset($validated['airline']) ? (string) $validated['airline'] : null,
            origin: isset($validated['origin']) ? (string) $validated['origin'] : null,
            destination: isset($validated['destination']) ? (string) $validated['destination'] : null,
            departOn: isset($validated['depart_on']) ? (string) $validated['depart_on'] : null,
            returnOn: isset($validated['return_on']) ? (string) $validated['return_on'] : null,
            costAmount: Money::fromDecimal((string) ($validated['cost_amount'] ?? '0'))->toDecimalString(),
            sellAmount: Money::fromDecimal((string) ($validated['sell_amount'] ?? '0'))->toDecimalString(),
            commissionAmount: Money::fromDecimal((string) ($validated['commission_amount'] ?? '0'))->toDecimalString(),
            currency: strtoupper((string) ($validated['currency'] ?? 'BDT')),
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
            passengers: $passengerData,
            segments: $segmentData,
            hotelStays: $hotelData,
            itinerary: $itineraryData,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'customer_id' => $this->customerId,
            'handled_by_employee_id' => $this->handledByEmployeeId,
            'type' => $this->type,
            'title' => $this->title,
            'supplier_name' => $this->supplierName,
            'pnr' => $this->pnr,
            'airline' => $this->airline,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'depart_on' => $this->departOn,
            'return_on' => $this->returnOn,
            'cost_amount' => $this->costAmount,
            'sell_amount' => $this->sellAmount,
            'commission_amount' => $this->commissionAmount,
            'currency' => $this->currency,
            'notes' => $this->notes,
        ];
    }
}
