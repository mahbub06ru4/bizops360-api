<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\PropertyMatchStatus;
use App\Modules\Industry\RealEstate\Domain\UnitPriceType;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\PropertyMatch;
use App\Modules\Industry\RealEstate\Models\PropertyRequirement;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Rule-based (not AI/NLP — that is roadmap Phase 4) scorer that suggests
 * available units for a {@see PropertyRequirement}: budget proximity, a
 * free-form location string match, and minimum bedrooms. Persists a
 * {@see PropertyMatch} per candidate unit (idempotent per requirement/unit
 * pair — re-running refreshes the score) and returns them ordered best-first.
 */
class MatchRequirementToUnits
{
    use InteractsWithTenant;

    private const MAX_CANDIDATES = 20;

    public function __construct(private readonly TenantContext $context) {}

    /**
     * @return EloquentCollection<int, PropertyMatch>
     */
    public function handle(PropertyRequirement $requirement): EloquentCollection
    {
        $this->assertTenantOwns($requirement);

        $tenantId = $this->currentTenantId();

        $units = $this->candidateUnits($tenantId, $requirement);

        $matches = DB::transaction(function () use ($requirement, $units): EloquentCollection {
            $matches = $units->map(function (Unit $unit) use ($requirement): PropertyMatch {
                $score = $this->score($requirement, $unit);

                /** @var PropertyMatch $match */
                $match = PropertyMatch::query()->updateOrCreate(
                    [
                        'tenant_id' => $requirement->tenant_id,
                        'requirement_id' => $requirement->getKey(),
                        'unit_id' => $unit->getKey(),
                    ],
                    [
                        'match_score' => $score,
                        'status' => PropertyMatchStatus::Suggested,
                    ],
                );

                // The candidate unit is already fully loaded (prices, building,
                // project, locations) — reuse it instead of a second query.
                $match->setRelation('unit', $unit);

                return $match;
            });

            return new EloquentCollection($matches->all());
        });

        return $matches->sortByDesc(fn (PropertyMatch $match): float => (float) $match->match_score)->values();
    }

    /**
     * @return EloquentCollection<int, Unit>
     */
    private function candidateUnits(int $tenantId, PropertyRequirement $requirement): EloquentCollection
    {
        $query = Unit::query()
            ->where('tenant_id', $tenantId)
            ->where('status', UnitStatus::Available->value)
            ->with(['prices', 'building.project.locations']);

        if ($requirement->bedrooms_min !== null) {
            $query->where('bedrooms', '>=', $requirement->bedrooms_min);
        }

        if ($requirement->unit_type !== null) {
            $query->whereHas('building.project', function ($projectQuery) use ($requirement): void {
                $projectQuery->where('project_type', $requirement->unit_type);
            });
        }

        /** @var EloquentCollection<int, Unit> $units */
        $units = $query->get();

        if ($requirement->budget_min !== null || $requirement->budget_max !== null) {
            $units = $units->filter(function (Unit $unit) use ($requirement): bool {
                $price = $this->currentPrice($unit);

                if ($price === null) {
                    return false;
                }

                if ($requirement->budget_min !== null && $price < (float) $requirement->budget_min) {
                    return false;
                }

                if ($requirement->budget_max !== null && $price > (float) $requirement->budget_max) {
                    return false;
                }

                return true;
            });
        }

        return $units->take(self::MAX_CANDIDATES)->values();
    }

    private function score(PropertyRequirement $requirement, Unit $unit): string
    {
        $score = 0.0;

        $price = $this->currentPrice($unit);

        if ($requirement->budget_min !== null || $requirement->budget_max !== null) {
            $min = $requirement->budget_min !== null ? (float) $requirement->budget_min : (float) $requirement->budget_max;
            $max = $requirement->budget_max !== null ? (float) $requirement->budget_max : (float) $requirement->budget_min;
            $midpoint = ($min + $max) / 2;
            $range = max($max - $min, $midpoint * 0.3, 1.0);

            if ($price !== null) {
                $distance = abs($price - $midpoint);
                $score += 60 * max(0.0, 1 - ($distance / $range));
            }
        } else {
            $score += 30.0;
        }

        $locations = $requirement->preferred_locations;
        if ($locations !== null && $locations !== '') {
            $score += $this->locationMatches($locations, $unit) ? 25.0 : 0.0;
        } else {
            $score += 12.0;
        }

        if ($requirement->bedrooms_min !== null) {
            $score += $unit->bedrooms !== null && $unit->bedrooms >= $requirement->bedrooms_min ? 15.0 : 0.0;
        } else {
            $score += 8.0;
        }

        return number_format(min(100.0, max(0.0, $score)), 2, '.', '');
    }

    private function currentPrice(Unit $unit): ?float
    {
        $current = $unit->prices->firstWhere('price_type', UnitPriceType::Current);
        $base = $unit->prices->firstWhere('price_type', UnitPriceType::Base);

        $price = $current ?? $base ?? $unit->prices->first();

        return $price !== null ? (float) $price->price : null;
    }

    private function locationMatches(string $preferredLocations, Unit $unit): bool
    {
        $needles = array_filter(array_map(
            static fn (string $needle): string => trim(Str::lower($needle)),
            preg_split('/[,;]+/', $preferredLocations) ?: [],
        ));

        if ($needles === []) {
            return false;
        }

        $haystacks = $unit->building?->project?->locations
            ->flatMap(fn ($location): array => [$location->area, $location->district, $location->division, $location->sector])
            ->filter()
            ->map(static fn (string $value): string => Str::lower($value))
            ->all() ?? [];

        foreach ($needles as $needle) {
            foreach ($haystacks as $haystack) {
                if (str_contains($haystack, $needle) || str_contains($needle, $haystack)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
