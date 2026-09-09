<?php

declare(strict_types=1);

namespace App\Modules\HR\Database\Factories;

use App\Modules\HR\Models\AttendanceSetting;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSetting>
 */
class AttendanceSettingFactory extends Factory
{
    protected $model = AttendanceSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'work_starts_at' => '09:00:00',
            'work_ends_at' => '17:00:00',
            'grace_minutes' => 15,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes): array => ['tenant_id' => $tenant->getKey()]);
    }
}
