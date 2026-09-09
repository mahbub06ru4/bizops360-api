<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Data;

use App\Modules\Industry\Travel\Domain\VisaStage;

/**
 * Application input for recording an embassy decision on a visa application.
 */
final readonly class RecordVisaDecisionData
{
    public function __construct(
        public VisaStage $outcome,
        public string $decisionOn,
        public ?string $decisionNote,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            outcome: VisaStage::from((string) $validated['outcome']),
            decisionOn: (string) $validated['decision_on'],
            decisionNote: isset($validated['decision_note']) ? (string) $validated['decision_note'] : null,
        );
    }
}
