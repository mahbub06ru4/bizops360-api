<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Domain\BookingStatus;
use App\Modules\Industry\Travel\Domain\BookingType;

it('marks only quoted and confirmed bookings editable', function (): void {
    expect(BookingStatus::Quoted->isEditable())->toBeTrue()
        ->and(BookingStatus::Confirmed->isEditable())->toBeTrue()
        ->and(BookingStatus::Ticketed->isEditable())->toBeFalse()
        ->and(BookingStatus::Refunded->isEditable())->toBeFalse();
});

it('knows which statuses are cancelled and which are still active', function (): void {
    expect(BookingStatus::Cancelled->isCancelled())->toBeTrue()
        ->and(BookingStatus::Refunded->isCancelled())->toBeTrue()
        ->and(BookingStatus::Ticketed->isCancelled())->toBeFalse()
        ->and(BookingStatus::Ticketed->isActive())->toBeTrue()
        ->and(BookingStatus::Cancelled->isActive())->toBeFalse();
});

it('requires a PNR only for air tickets', function (): void {
    expect(BookingType::AirTicket->requiresPnr())->toBeTrue()
        ->and(BookingType::Hotel->requiresPnr())->toBeFalse()
        ->and(BookingType::Umrah->requiresPnr())->toBeFalse();
});

it('supports an itinerary only for tour, umrah and hajj packages', function (): void {
    expect(BookingType::TourPackage->supportsItinerary())->toBeTrue()
        ->and(BookingType::Umrah->supportsItinerary())->toBeTrue()
        ->and(BookingType::Hajj->supportsItinerary())->toBeTrue()
        ->and(BookingType::AirTicket->supportsItinerary())->toBeFalse();
});
