<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\Industry\RealEstate\Domain\VerificationDecision;
use App\Modules\Industry\RealEstate\Models\RealEstateProject;
use App\Modules\Industry\RealEstate\Models\VerificationReview;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerificationReview>
 */
class VerificationReviewFactory extends Factory
{
    protected $model = VerificationReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => RealEstateProject::factory(),
            'reviewed_by' => null,
            'decision' => VerificationDecision::Pending,
            'notes' => null,
            'reviewed_at' => null,
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
