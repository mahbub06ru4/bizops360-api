<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Domain\UnitMediaType;

/**
 * Application input for attaching a media row to a unit — the binary itself
 * is handled separately as an {@see \Illuminate\Http\UploadedFile} by
 * {@see \App\Modules\Industry\RealEstate\Actions\AddUnitMedia}.
 */
final readonly class UnitMediaData
{
    public function __construct(
        public UnitMediaType $mediaType,
        public int $sortOrder,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            mediaType: UnitMediaType::from((string) ($validated['media_type'] ?? UnitMediaType::Image->value)),
            sortOrder: (int) ($validated['sort_order'] ?? 0),
        );
    }
}
