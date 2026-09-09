<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Actions\Concerns\InteractsWithTenant;
use App\Modules\CRM\Data\CrmNoteData;
use App\Modules\CRM\Models\CrmActivity;
use App\Modules\CRM\Support\RecordsCrmActivity;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Records a free-text note against a lead or customer as a {@see CrmActivity}.
 */
class AddCrmNote
{
    use InteractsWithTenant;
    use RecordsCrmActivity;

    public function __construct(private readonly TenantContext $context) {}

    public function handle(Model $subject, CrmNoteData $data, User $author): CrmActivity
    {
        $this->assertTenantOwns($subject);

        return $this->recordCrmActivity(
            $subject,
            'note',
            Str::limit($data->body, 200),
            (int) $author->getKey(),
            ['note' => $data->body],
        );
    }

    protected function tenantContext(): TenantContext
    {
        return $this->context;
    }
}
