<?php

declare(strict_types=1);

use App\Modules\Industry\Travel\Domain\VisaStage;

it('exposes its values as a plain list', function (): void {
    expect(VisaStage::values())->toBe([
        'draft', 'documents_pending', 'documents_collected',
        'submitted', 'processing', 'approved', 'rejected', 'cancelled',
    ]);
});

it('knows which stages are closed', function (): void {
    expect(VisaStage::Approved->isClosed())->toBeTrue()
        ->and(VisaStage::Rejected->isClosed())->toBeTrue()
        ->and(VisaStage::Cancelled->isClosed())->toBeTrue()
        ->and(VisaStage::Processing->isClosed())->toBeFalse()
        ->and(VisaStage::Draft->isClosed())->toBeFalse();
});

it('allows only forward transitions through the pipeline', function (): void {
    expect(VisaStage::DocumentsCollected->canTransitionTo(VisaStage::Submitted))->toBeTrue()
        ->and(VisaStage::Submitted->canTransitionTo(VisaStage::Processing))->toBeTrue()
        ->and(VisaStage::Submitted->canTransitionTo(VisaStage::Approved))->toBeTrue()
        ->and(VisaStage::Processing->canTransitionTo(VisaStage::Rejected))->toBeTrue();
});

it('rejects skipping or reversing stages', function (): void {
    expect(VisaStage::Draft->canTransitionTo(VisaStage::Submitted))->toBeFalse()
        ->and(VisaStage::DocumentsPending->canTransitionTo(VisaStage::Approved))->toBeFalse()
        ->and(VisaStage::Approved->canTransitionTo(VisaStage::Processing))->toBeFalse()
        ->and(VisaStage::Processing->canTransitionTo(VisaStage::Submitted))->toBeFalse();
});
