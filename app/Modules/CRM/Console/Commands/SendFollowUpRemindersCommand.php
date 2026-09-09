<?php

declare(strict_types=1);

namespace App\Modules\CRM\Console\Commands;

use App\Modules\CRM\Actions\SendFollowUpReminders;
use Illuminate\Console\Command;

class SendFollowUpRemindersCommand extends Command
{
    protected $signature = 'crm:send-followup-reminders';

    protected $description = 'Notify assignees of CRM follow-ups coming due';

    public function handle(SendFollowUpReminders $action): int
    {
        $sent = $action->handle();

        $this->info("Sent {$sent} follow-up reminder(s).");

        return self::SUCCESS;
    }
}
