<?php

declare(strict_types=1);

namespace App\Modules\Billing\Data;

final readonly class ChangePlanData
{
    public function __construct(public string $planCode) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(planCode: (string) $validated['plan_code']);
    }
}
