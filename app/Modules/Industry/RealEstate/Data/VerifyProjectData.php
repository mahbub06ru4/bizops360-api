<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for verifying a project's submission.
 */
final readonly class VerifyProjectData
{
    public function __construct(
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            notes: isset($validated['notes']) ? (string) $validated['notes'] : null,
        );
    }
}
