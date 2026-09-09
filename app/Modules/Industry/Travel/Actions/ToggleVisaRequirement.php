<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Domain\VisaStage;
use App\Modules\Industry\Travel\Models\VisaApplication;
use App\Modules\Industry\Travel\Models\VisaRequirement;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Marks a checklist line collected or not, and rolls the application's stage
 * forward to `documents_collected` once every mandatory line is in.
 */
class ToggleVisaRequirement
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(VisaRequirement $requirement, bool $collected, ?string $collectedOn = null, ?string $note = null): VisaRequirement
    {
        $this->assertTenantOwns($requirement);

        return DB::transaction(function () use ($requirement, $collected, $collectedOn, $note): VisaRequirement {
            $requirement->collected = $collected;
            $requirement->collected_on = $collected
                ? Carbon::parse($collectedOn ?? Carbon::now()->toDateString())
                : null;

            if ($note !== null) {
                $requirement->note = $note;
            }

            $requirement->save();

            /** @var VisaApplication $application */
            $application = $requirement->visaApplication()->firstOrFail();
            $this->syncStage($application);

            return $requirement->refresh();
        });
    }

    private function syncStage(VisaApplication $application): void
    {
        if (! in_array($application->stage, [VisaStage::Draft, VisaStage::DocumentsPending, VisaStage::DocumentsCollected], true)) {
            return;
        }

        $mandatoryOutstanding = $application->requirements()
            ->where('is_mandatory', true)
            ->where('collected', false)
            ->exists();

        $target = $mandatoryOutstanding ? VisaStage::DocumentsPending : VisaStage::DocumentsCollected;

        if ($application->stage !== $target) {
            $application->stage = $target;
            $application->save();
        }
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
