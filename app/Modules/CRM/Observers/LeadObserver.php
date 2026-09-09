<?php

declare(strict_types=1);

namespace App\Modules\CRM\Observers;

use App\Modules\CRM\Domain\LeadStage;
use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Support\RecordsCrmActivity;
use Illuminate\Support\Facades\Auth;

class LeadObserver
{
    use RecordsCrmActivity;

    public function created(Lead $lead): void
    {
        $this->recordCrmActivity($lead, 'created', 'Lead created', $this->causerId());
    }

    public function updated(Lead $lead): void
    {
        if ($lead->wasChanged('stage')) {
            $from = $lead->getOriginal('stage');
            $from = $from instanceof LeadStage ? $from->value : (string) $from;

            $this->recordCrmActivity(
                $lead,
                'stage_changed',
                "Stage changed to {$lead->stage->value}",
                $this->causerId(),
                ['from' => $from, 'to' => $lead->stage->value],
            );
        }
    }

    public function deleting(Lead $lead): void
    {
        $lead->contacts()->delete();
        $lead->activities()->delete();
    }

    private function causerId(): ?int
    {
        $id = Auth::id();

        return is_numeric($id) ? (int) $id : null;
    }
}
