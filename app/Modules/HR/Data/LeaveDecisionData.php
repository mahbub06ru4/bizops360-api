<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

/**
 * Application input for approving or rejecting a leave request.
 */
final readonly class LeaveDecisionData
{
    public function __construct(public ?string $note) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            note: isset($validated['note']) ? (string) $validated['note'] : null,
        );
    }
}
