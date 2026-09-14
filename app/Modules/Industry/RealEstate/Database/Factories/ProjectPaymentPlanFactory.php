<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\PaymentPlanFrequency;
use App\Modules\Industry\RealEstate\Models\ProjectPaymentPlan;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectPaymentPlan>
 */
class ProjectPaymentPlanFactory extends Factory
{
    protected $model = ProjectPaymentPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'name' => 'Standard Plan',
            'down_payment_percent' => 20,
            'installment_count' => 36,
            'installment_frequency' => PaymentPlanFrequency::Monthly,
        ];
    }

    public function forProject(RealEstateProject $project): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->getKey(),
        ]);
    }
}
