<?php

declare(strict_types=1);

namespace App\Modules\Operations\Data;

/**
 * Application input for a task comment.
 */
final readonly class TaskCommentData
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
