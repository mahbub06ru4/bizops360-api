<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Data;

use App\Modules\Industry\RealEstate\Actions\UploadProjectDocument;
use App\Modules\Industry\RealEstate\Domain\ProjectDocumentType;
use Illuminate\Http\UploadedFile;

/**
 * Application input for uploading a project document — the binary itself is
 * handled separately as an {@see UploadedFile} by
 * {@see UploadProjectDocument}.
 */
final readonly class ProjectDocumentData
{
    public function __construct(
        public ProjectDocumentType $documentType,
        public bool $isPrivate,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            documentType: ProjectDocumentType::from((string) ($validated['document_type'] ?? ProjectDocumentType::Other->value)),
            isPrivate: (bool) ($validated['is_private'] ?? true),
        );
    }
}
