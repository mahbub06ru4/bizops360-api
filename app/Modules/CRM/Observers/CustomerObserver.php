<?php

declare(strict_types=1);

namespace App\Modules\CRM\Observers;

use App\Modules\CRM\Models\Customer;
use App\Modules\CRM\Support\RecordsCrmActivity;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    use RecordsCrmActivity;

    public function created(Customer $customer): void
    {
        $id = Auth::id();

        $this->recordCrmActivity(
            $customer,
            'created',
            'Customer created',
            is_numeric($id) ? (int) $id : null,
        );
    }

    public function deleting(Customer $customer): void
    {
        $customer->contacts()->delete();
        $customer->activities()->delete();
        $customer->followups()->delete();
    }
}
