<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Database\Factories;

use App\Modules\CRM\Models\Lead;
use App\Modules\Industry\RealEstate\Domain\OfferedBy;
use App\Modules\Industry\RealEstate\Domain\OfferStatus;
use App\Modules\Industry\RealEstate\Models\Offer;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'lead_id' => Lead::factory(),
            'unit_id' => Unit::factory(),
            'previous_offer_id' => null,
            'offered_price' => fake()->randomFloat(2, 5000000, 15000000),
            'offered_by' => OfferedBy::Buyer,
            'status' => OfferStatus::Pending,
            'notes' => null,
        ];
    }

    public function forLeadAndUnit(Lead $lead, Unit $unit): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->getKey(),
            'unit_id' => $unit->getKey(),
        ]);
    }
}
