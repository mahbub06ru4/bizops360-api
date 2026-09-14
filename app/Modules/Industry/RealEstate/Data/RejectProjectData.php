<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for rejecting a project's verification submission.
 */
final readonly class RejectProjectData
{
    public function __construct(
        public string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            notes: (string) $validated['notes'],
        );
    }
}
