<?php

declare(strict_types=1);

namespace App\Modules\HR\Database\Factories;

use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveType;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveBalance>
 */
class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => 2026,
            'entitled_days' => 20,
            'used_days' => 0,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
