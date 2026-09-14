<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Actions\AddUnitMedia;
use App\Modules\Industry\RealEstate\Domain\UnitMediaType;
use Illuminate\Http\UploadedFile;

/**
 * Application input for attaching a media row to a unit — the binary itself
 * is handled separately as an {@see UploadedFile} by
 * {@see AddUnitMedia}.
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
