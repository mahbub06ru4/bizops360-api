<?php

declare(strict_types=1);

namespace App\Modules\CRM\Support;

use App\Modules\CRM\Models\CrmActivity;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an immutable {@see CrmActivity} row against a lead or customer.
 */
trait RecordsCrmActivity
{
    /**
     * @param  array<string, mixed>  $properties
     */
    protected function recordCrmActivity(
        Model $subject,
        string $event,
        string $description,
        ?int $causerId = null,
        array $properties = [],
    ): CrmActivity {
        $activity = new CrmActivity([
            'event' => $event,
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
        ]);
        $activity->tenant_id = (int) $subject->getAttribute('tenant_id');
        $activity->subject_type = $subject->getMorphClass();
        $activity->subject_id = (int) $subject->getKey();
        $activity->causer_id = $causerId;
        $activity->save();

        return $activity;
    }
}
