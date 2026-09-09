<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

/**
 * Application input for completing a follow-up.
 */
final readonly class FollowUpOutcomeData
{
    public function __construct(public ?string $outcome) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            outcome: isset($validated['outcome']) ? (string) $validated['outcome'] : null,
        );
    }
}
