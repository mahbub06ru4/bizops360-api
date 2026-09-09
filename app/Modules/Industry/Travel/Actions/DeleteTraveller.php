<?php

declare(strict_types=1);

namespace App\Modules\Industry\Travel\Actions;

use App\Modules\Industry\Travel\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\Travel\Models\Traveller;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteTraveller
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Traveller $traveller): void
    {
        $this->assertTenantOwns($traveller);

        if ($traveller->bookingPassengers()->exists() || $traveller->visaApplications()->exists()) {
            throw ValidationException::withMessages([
                'traveller' => 'This traveller is attached to a booking or visa application and cannot be deleted.',
            ]);
        }

        $traveller->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
