<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Modules\HR\Database\Factories\AttendanceSettingFactory;
use App\Modules\HR\Domain\WorkSchedule;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A tenant's working-hours configuration (one row per tenant).
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $work_starts_at
 * @property string $work_ends_at
 * @property int $grace_minutes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['work_starts_at', 'work_ends_at', 'grace_minutes'])]
class AttendanceSetting extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AttendanceSettingFactory> */
    use HasFactory;

    public function schedule(): WorkSchedule
    {
        return new WorkSchedule($this->work_starts_at, $this->work_ends_at, $this->grace_minutes);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_starts_at' => 'string',
            'work_ends_at' => 'string',
            'grace_minutes' => 'integer',
        ];
    }

    protected static function newFactory(): AttendanceSettingFactory
    {
        return AttendanceSettingFactory::new();
    }
}
