<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

use App\Modules\CRM\Domain\LeadStage;

/**
 * Application input for moving a lead to a new pipeline stage.
 */
final readonly class LeadStageData
{
    public function __construct(
        public LeadStage $stage,
        public ?string $lostReason,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            stage: LeadStage::from((string) $validated['stage']),
            lostReason: isset($validated['lost_reason']) ? (string) $validated['lost_reason'] : null,
        );
    }
}
