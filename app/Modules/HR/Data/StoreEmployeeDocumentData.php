<?php

declare(strict_types=1);

namespace App\Modules\HR\Data;

use App\Modules\HR\Domain\EmployeeDocumentCategory;

/**
 * Application input for attaching a document to an employee (metadata only — the
 * uploaded file is passed to the action separately).
 */
final readonly class StoreEmployeeDocumentData
{
    public function __construct(
        public int $employeeId,
        public EmployeeDocumentCategory $category,
        public string $title,
        public ?string $expiresAt,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            employeeId: (int) $validated['employee_id'],
            category: EmployeeDocumentCategory::from((string) $validated['category']),
            title: (string) $validated['title'],
            expiresAt: isset($validated['expires_at']) ? (string) $validated['expires_at'] : null,
        );
    }
}
