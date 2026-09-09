<?php

declare(strict_types=1);

use App\Modules\Organization\Actions\UpdateBranch;
use App\Modules\Organization\Data\BranchData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('refuses to update a branch that belongs to another tenant', function (): void {
    $tenantA = makeTenant(['slug' => 'guard-a']);
    makeUser($tenantA, 'owner');

    $tenantB = makeTenant(['slug' => 'guard-b']);
    $foreign = Branch::factory()->forTenant($tenantB)->create();

    app(TenantContext::class)->set($tenantA->fresh());

    expect(fn () => app(UpdateBranch::class)->handle(
        $foreign,
        new BranchData('Hijacked', 'HJ', null, null, null, false),
    ))->toThrow(ModelNotFoundException::class);
});
