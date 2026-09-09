<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Finance\Domain\Money;

/**
 * Application input for opening or updating a VisaApplication.
 */
final readonly class VisaApplicationData
{
    public function __construct(
        public int $travellerId,
        public ?int $customerId,
        public ?int $assignedEmployeeId,
        public string $destinationCountry,
        public string $visaType,
        public ?string $mission,
        public ?string $referenceNo,
        public ?string $applicationNo,
        public string $governmentFee,
        public string $serviceCharge,
        public ?string $expectedTravelDate,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            travellerId: (int) $validated['traveller_id'],
            customerId: isset($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            assignedEmployeeId: isset($validated['assigned_employee_id']) ? (int) $validated['assigned_employee_id'] : null,
            destinationCountry: (string) $validated['destination_country'],
            visaType: (string) ($validated['visa_type'] ?? 'tourist'),
            mission: isset($validated['mission']) ? (string) $validated['mission'] : null,
            referenceNo: isset($validated['reference_no']) ? (string) $validated['reference_no'] : null,
            applicationNo: isset($validated['application_no']) ? (string) $validated['application_no'] : null,
            governmentFee: Money::fromDecimal((string) ($validated['government_fee'] ?? '0'))->toDecimalString(),
            serviceCharge: Money::fromDecimal((string) ($validated['service_charge'] ?? '0'))->toDecimalString(),
            expectedTravelDate: isset($validated['expected_travel_date']) ? (string) $validated['expected_travel_date'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'customer_id' => $this->customerId,
            'assigned_employee_id' => $this->assignedEmployeeId,
            'destination_country' => $this->destinationCountry,
            'visa_type' => $this->visaType,
            'mission' => $this->mission,
            'reference_no' => $this->referenceNo,
            'application_no' => $this->applicationNo,
            'government_fee' => $this->governmentFee,
            'service_charge' => $this->serviceCharge,
            'expected_travel_date' => $this->expectedTravelDate,
        ];
    }
}
