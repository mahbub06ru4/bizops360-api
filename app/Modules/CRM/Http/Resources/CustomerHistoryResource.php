<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Resources;

use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\CRM\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        /** @var Customer $customer */
        $customer = $data['customer'];
        $sourceLead = $data['source_lead'] instanceof Lead ? $data['source_lead'] : null;

        /** @var Collection<int, Contact> $contacts */
        $contacts = $data['contacts'];
        /** @var Collection<int, FollowUp> $followUps */
        $followUps = $data['follow_ups'];
        /** @var Collection<int, CrmActivity> $activities */
        $activities = $data['activities'];

        return [
            'customer' => CustomerResource::make($customer),
            'source_lead' => $sourceLead !== null ? LeadResource::make($sourceLead) : null,
            'contacts' => ContactResource::collection($contacts),
            'follow_ups' => FollowUpResource::collection($followUps),
            'activities' => CrmActivityResource::collection($activities),
        ];
    }
}
