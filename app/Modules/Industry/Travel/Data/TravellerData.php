<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Industry\Travel\Domain\TravellerGender;

/**
 * Application input for creating or updating a Traveller.
 */
final readonly class TravellerData
{
    public function __construct(
        public ?int $customerId,
        public string $fullName,
        public TravellerGender $gender,
        public ?string $dateOfBirth,
        public string $nationality,
        public ?string $passportNumber,
        public ?string $passportExpiry,
        public ?string $passportIssueCountry,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            customerId: isset($validated['customer_id']) ? (int) $validated['customer_id'] : null,
            fullName: (string) $validated['full_name'],
            gender: TravellerGender::from((string) ($validated['gender'] ?? TravellerGender::Other->value)),
            dateOfBirth: isset($validated['date_of_birth']) ? (string) $validated['date_of_birth'] : null,
            nationality: (string) ($validated['nationality'] ?? 'Bangladeshi'),
            passportNumber: isset($validated['passport_number']) ? (string) $validated['passport_number'] : null,
            passportExpiry: isset($validated['passport_expiry']) ? (string) $validated['passport_expiry'] : null,
            passportIssueCountry: isset($validated['passport_issue_country']) ? (string) $validated['passport_issue_country'] : null,
            phone: isset($validated['phone']) ? (string) $validated['phone'] : null,
            email: isset($validated['email']) ? (string) $validated['email'] : null,
            address: isset($validated['address']) ? (string) $validated['address'] : null,
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'customer_id' => $this->customerId,
            'full_name' => $this->fullName,
            'gender' => $this->gender,
            'date_of_birth' => $this->dateOfBirth,
            'nationality' => $this->nationality,
            'passport_number' => $this->passportNumber,
            'passport_expiry' => $this->passportExpiry,
            'passport_issue_country' => $this->passportIssueCountry,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'notes' => $this->notes,
        ];
    }
}
