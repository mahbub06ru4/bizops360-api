<?php

declare(strict_types=1);

namespace App\Modules\CRM\Actions;

use App\Models\User;
use App\Modules\CRM\Domain\FollowUpStatus;
use App\Modules\CRM\Models\FollowUp;
use App\Modules\Notifications\Notifications\FollowUpDueNotification;
use App\Modules\Organization\Models\Employee;
use Illuminate\Support\Carbon;

/**
 * Notifies assignees (or creators) of pending follow-ups that fall due within
 * the reminder window and have not been reminded yet. Runs across all tenants
 * from a scheduled command — no tenant context is bound.
 */
class SendFollowUpReminders
{
    private const int WINDOW_MINUTES = 30;

    public function handle(?Carbon $now = null): int
    {
        $now ??= Carbon::now();
        $cutoff = $now->copy()->addMinutes(self::WINDOW_MINUTES);

        $followUps = FollowUp::query()
            ->withoutGlobalScopes()
            ->where('status', FollowUpStatus::Pending->value)
            ->whereNull('reminder_sent_at')
            ->where('due_at', '<=', $cutoff)
            ->with('followupable')
            ->get();

        $sent = 0;

        foreach ($followUps as $followUp) {
            if ($this->notify($followUp)) {
                $sent++;
            }

            $followUp->reminder_sent_at = $now;
            $followUp->save();
        }

        return $sent;
    }

    private function notify(FollowUp $followUp): bool
    {
        $userId = $this->recipientUserId($followUp);

        if ($userId === null) {
            return false;
        }

        $user = User::query()->withoutGlobalScopes()->whereKey($userId)->first();

        if ($user === null) {
            return false;
        }

        $subject = $followUp->followupable;
        $label = $subject !== null ? (string) $subject->getAttribute('name') : 'a record';

        $user->notify(new FollowUpDueNotification(
            (int) $followUp->getKey(),
            $label,
            $followUp->type->value,
            $followUp->due_at->toIso8601String(),
            $followUp->notes,
        ));

        return true;
    }

    private function recipientUserId(FollowUp $followUp): ?int
    {
        if ($followUp->assigned_employee_id !== null) {
            $id = Employee::query()->withoutGlobalScopes()
                ->whereKey($followUp->assigned_employee_id)->value('user_id');

            return $id === null ? null : (int) $id;
        }

        return $followUp->created_by;
    }
}
