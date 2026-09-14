<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

/**
 * Application input for completing a site visit.
 */
final readonly class CompleteSiteVisitData
{
    public function __construct(
        public ?string $feedback,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            feedback: isset($validated['feedback']) ? (string) $validated['feedback'] : null,
        );
    }
}
