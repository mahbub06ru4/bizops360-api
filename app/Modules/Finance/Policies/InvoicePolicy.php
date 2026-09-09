<?php

declare(strict_types=1);

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.view') && $this->sameTenant($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->can('invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.update') && $this->sameTenant($user, $invoice);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.delete') && $this->sameTenant($user, $invoice);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.send') && $this->sameTenant($user, $invoice);
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.void') && $this->sameTenant($user, $invoice);
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.record_payment') && $this->sameTenant($user, $invoice);
    }

    public function refund(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.refund') && $this->sameTenant($user, $invoice);
    }

    private function sameTenant(User $user, Invoice $invoice): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $invoice->tenant_id;
    }
}
