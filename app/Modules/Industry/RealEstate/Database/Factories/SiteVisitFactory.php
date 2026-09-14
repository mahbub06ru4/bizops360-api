<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Domain\SiteVisitStatus;
use App\Modules\Industry\RealEstate\Models\SiteVisit;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteVisit>
 */
class SiteVisitFactory extends Factory
{
    protected $model = SiteVisit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'unit_id' => Unit::factory(),
            'project_id' => null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'status' => SiteVisitStatus::Scheduled,
            'conducted_by_employee_id' => null,
            'feedback' => null,
        ];
    }

    public function forLead(Lead $lead): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->getKey(),
        ]);
    }
}
