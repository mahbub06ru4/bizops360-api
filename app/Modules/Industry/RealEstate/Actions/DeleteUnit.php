<?php

declare(strict_types=1);

namespace App\Modules\Industry\RealEstate\Actions;

use App\Modules\Industry\RealEstate\Actions\Concerns\InteractsWithTenant;
use App\Modules\Industry\RealEstate\Domain\UnitStatus;
use App\Modules\Industry\RealEstate\Models\Unit;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Validation\ValidationException;

class DeleteUnit
{
    use InteractsWithTenant;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Unit $unit): void
    {
        $this->assertTenantOwns($unit);

        if ($unit->status !== UnitStatus::Available) {
            throw ValidationException::withMessages([
                'unit' => 'Only an available unit can be deleted; reserved or sold units must stay for the record.',
            ]);
        }

        $unit->delete();
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
