<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Http\Requests;

use App\Modules\Industry\Travel\Data\BookingData;
use App\Modules\Industry\Travel\Domain\BoardBasis;
use App\Modules\Industry\Travel\Domain\BookingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;
        $tenantScoped = fn (string $table) => Rule::exists($table, 'id')
            ->where(fn ($query) => $query->where('tenant_id', $tenantId));

        return [
            'customer_id' => ['nullable', 'integer', $tenantScoped('customers')],
            'handled_by_employee_id' => ['nullable', 'integer', $tenantScoped('employees')],
            'type' => ['sometimes', Rule::in(BookingType::values())],
            'title' => ['required', 'string', 'max:255'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'pnr' => ['nullable', 'string', 'max:20'],
            'airline' => ['nullable', 'string', 'max:100'],
            'origin' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'string', 'max:100'],
            'depart_on' => ['nullable', 'date'],
            'return_on' => ['nullable', 'date', 'after_or_equal:depart_on'],
            'cost_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'sell_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'commission_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'passengers' => ['sometimes', 'array'],
            'passengers.*.traveller_id' => ['required_with:passengers', 'integer', $tenantScoped('travellers')],
            'passengers.*.ticket_number' => ['nullable', 'string', 'max:50'],
            'passengers.*.baggage' => ['nullable', 'string', 'max:50'],
            'passengers.*.fare_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],

            'segments' => ['sometimes', 'array'],
            'segments.*.flight_number' => ['required_with:segments', 'string', 'max:20'],
            'segments.*.airline' => ['nullable', 'string', 'max:100'],
            'segments.*.from_airport' => ['required_with:segments', 'string', 'max:10'],
            'segments.*.to_airport' => ['required_with:segments', 'string', 'max:10'],
            'segments.*.depart_at' => ['required_with:segments', 'date'],
            'segments.*.arrive_at' => ['nullable', 'date'],
            'segments.*.cabin' => ['nullable', 'string', 'max:20'],

            'hotel_stays' => ['sometimes', 'array'],
            'hotel_stays.*.hotel_name' => ['required_with:hotel_stays', 'string', 'max:255'],
            'hotel_stays.*.city' => ['required_with:hotel_stays', 'string', 'max:100'],
            'hotel_stays.*.country' => ['nullable', 'string', 'max:100'],
            'hotel_stays.*.check_in' => ['required_with:hotel_stays', 'date'],
            'hotel_stays.*.check_out' => ['required_with:hotel_stays', 'date', 'after:hotel_stays.*.check_in'],
            'hotel_stays.*.room_type' => ['nullable', 'string', 'max:100'],
            'hotel_stays.*.rooms' => ['nullable', 'integer', 'min:1', 'max:100'],
            'hotel_stays.*.guests' => ['nullable', 'integer', 'min:1', 'max:100'],
            'hotel_stays.*.board_basis' => ['sometimes', Rule::in(BoardBasis::values())],
            'hotel_stays.*.confirmation_no' => ['nullable', 'string', 'max:100'],
            'hotel_stays.*.note' => ['nullable', 'string', 'max:255'],

            'itinerary' => ['sometimes', 'array'],
            'itinerary.*.day_number' => ['nullable', 'integer', 'min:1', 'max:365'],
            'itinerary.*.title' => ['required_with:itinerary', 'string', 'max:255'],
            'itinerary.*.description' => ['nullable', 'string', 'max:255'],
            'itinerary.*.city' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toData(): BookingData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return BookingData::fromArray($validated);
    }
}
