<?php

declare(strict_types=1);

use App\Modules\Organization\Actions\UpdateDepartment;
use App\Modules\Organization\Data\DepartmentData;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Tenant\Context\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('hides another tenant\'s branch from index and returns 404 on direct access', function (): void {
    $tenantA = makeTenant(['slug' => 'iso-org-a']);
    $owner = makeUser($tenantA, 'owner');

    $tenantB = makeTenant(['slug' => 'iso-org-b']);
    $foreign = Branch::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($owner, 'sanctum')
        ->getJson('/api/v1/branches')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);

    $this->actingAs($owner, 'sanctum')->getJson("/api/v1/branches/{$foreign->id}")->assertNotFound();
    $this->actingAs($owner, 'sanctum')
        ->putJson("/api/v1/branches/{$foreign->id}", ['name' => 'Hijack', 'code' => 'H'])
        ->assertNotFound();
    $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/branches/{$foreign->id}")->assertNotFound();

    expect(Branch::withoutGlobalScopes()->find($foreign->id))->not->toBeNull();
});

it('will not attach a department to another tenant\'s branch even if validation is bypassed', function (): void {
    $tenantA = makeTenant(['slug' => 'iso-dept-a']);
    $owner = makeUser($tenantA, 'owner');
    $mine = Department::factory()->forTenant($tenantA)->create();

    $tenantB = makeTenant(['slug' => 'iso-dept-b']);
    $foreignBranch = Branch::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    // Re-bind tenant A and drive the action directly with a foreign branch id.
    app(TenantContext::class)->set($tenantA->fresh());

    expect(fn () => app(UpdateDepartment::class)->handle(
        $mine,
        new DepartmentData(
            branchId: $foreignBranch->id,
            name: $mine->name,
            code: $mine->code,
            description: null,
        ),
    ))->toThrow(ModelNotFoundException::class);
});
