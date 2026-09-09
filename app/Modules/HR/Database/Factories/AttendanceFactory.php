<?php

declare(strict_types=1);

namespace App\Modules\HR\Database\Factories;

use App\Modules\HR\Domain\AttendanceStatus;
use App\Modules\HR\Models\Attendance;
use App\Modules\Organization\Models\Employee;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::today();

        return [
            'tenant_id' => Tenant::factory(),
            'employee_id' => Employee::factory(),
            'date' => $date->toDateString(),
            'check_in_at' => $date->copy()->setTime(9, 0),
            'check_out_at' => $date->copy()->setTime(17, 0),
            'status' => AttendanceStatus::Present,
            'worked_minutes' => 480,
            'is_late' => false,
            'is_early_leave' => false,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }

    public function on(string $date): static
    {
        return $this->state(fn (array $attributes): array => ['date' => $date]);
    }

    public function status(AttendanceStatus $status): static
    {
        return $this->state(fn (array $attributes): array => ['status' => $status]);
    }
}
