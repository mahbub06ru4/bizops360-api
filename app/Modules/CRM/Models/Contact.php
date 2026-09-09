<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

use App\Modules\CRM\Database\Factories\ContactFactory;
use App\Modules\Tenant\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A person attached to a {@see Lead} or {@see Customer}.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $contactable_type
 * @property int $contactable_id
 * @property string $name
 * @property string|null $title
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_primary
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'title', 'email', 'phone', 'is_primary', 'notes'])]
class Contact extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }
}
