<?php

declare(strict_types=1);

namespace App\Modules\Organization\Data;

/**
 * Application input for replacing a team's member list.
 */
final readonly class TeamMembersData
{
    /**
     * @param  list<int>  $employeeIds
     */
    public function __construct(public array $employeeIds) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        /** @var list<int> $ids */
        $ids = array_values(array_map(static fn ($id): int => (int) $id, $validated['members'] ?? []));

        return new self($ids);
    }
}
