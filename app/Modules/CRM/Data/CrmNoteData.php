<?php

declare(strict_types=1);

namespace App\Modules\CRM\Data;

/**
 * Application input for a free-text note on a lead or customer.
 */
final readonly class CrmNoteData
{
    public function __construct(public string $body) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(body: (string) $validated['body']);
    }
}
