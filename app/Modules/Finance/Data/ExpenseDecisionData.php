<?php

declare(strict_types=1);

namespace App\Modules\Finance\Data;

use App\Modules\Finance\Models\Expense;

/**
 * Application input for approving or rejecting an {@see Expense}.
 */
final readonly class ExpenseDecisionData
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
