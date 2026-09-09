<?php

declare(strict_types=1);

namespace App\Modules\HR\Models;

use App\Modules\HR\Database\Factories\HolidayFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A non-working day for a tenant, used by leave-day calculation.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property Carbon $date
 * @property bool $is_recurring
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'date', 'is_recurring'])]
class Holiday extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<HolidayFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    protected static function newFactory(): HolidayFactory
    {
        return HolidayFactory::new();
    }
}
