<?php

declare(strict_types=1);

namespace App\Modules\CRM\Database\Factories;

use App\Modules\CRM\Domain\FollowUpStatus;
use App\Modules\CRM\Domain\FollowUpType;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FollowUp>
 */
class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'followupable_type' => (new Lead)->getMorphClass(),
            'followupable_id' => Lead::factory(),
            'assigned_employee_id' => null,
            'created_by' => null,
            'type' => FollowUpType::Call,
            'due_at' => Carbon::now()->addDay(),
            'status' => FollowUpStatus::Pending,
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function attachedTo(Model $followupable): static
    {
        return $this->state(fn (array $attributes): array => [
            'followupable_type' => $followupable->getMorphClass(),
            'followupable_id' => $followupable->getKey(),
        ]);
    }

    public function dueAt(string $when): static
    {
        return $this->state(fn (array $attributes): array => ['due_at' => $when]);
    }
}
