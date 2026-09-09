<?php

declare(strict_types=1);

use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads a document and stores the file on the disk', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'contract',
        'title' => 'Employment contract',
        'file' => UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf'),
    ])->assertCreated()
        ->assertJsonPath('data.category', 'contract')
        ->assertJsonPath('data.original_name', 'contract.pdf')
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonStructure(['data' => ['id', 'download_url']]);

    $document = EmployeeDocument::withoutGlobalScopes()->firstOrFail();
    expect($document->uploaded_by)->toBe($manager->id)
        ->and($document->disk)->toBe('local');
    Storage::disk('local')->assertExists($document->path);
});

it('validates the upload payload', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'contract',
        'title' => 'No file',
    ])->assertStatus(422)->assertJsonValidationErrors('file');
});

it('rejects an oversized or wrong-type file', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'contract',
        'title' => 'Bad',
        'file' => UploadedFile::fake()->create('script.exe', 50, 'application/octet-stream'),
    ])->assertStatus(422)->assertJsonValidationErrors('file');
});

it('rejects an upload for another tenant\'s employee', function (): void {
    Storage::fake('local');
    $other = makeTenant(['slug' => 'doc-o']);
    $foreign = Employee::factory()->forTenant($other)->create();
    $tenant = makeTenant(['slug' => 'doc-m']);
    $manager = makeUser($tenant, 'manager');
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $foreign->id,
        'category' => 'contract',
        'title' => 'X',
        'file' => UploadedFile::fake()->create('c.pdf', 10, 'application/pdf'),
    ])->assertStatus(422)->assertJsonValidationErrors('employee_id');
});

it('forbids staff from uploading documents', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $employee = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'contract',
        'title' => 'X',
        'file' => UploadedFile::fake()->create('c.pdf', 10, 'application/pdf'),
    ])->assertForbidden();
});

it('scopes the document list to the caller unless they may view all', function (): void {
    $tenant = makeTenant();
    $staff = makeUser($tenant, 'staff');
    $manager = makeUser($tenant, 'manager');
    $mine = Employee::factory()->forTenant($tenant)->create(['user_id' => $staff->id]);
    $other = Employee::factory()->forTenant($tenant)->create();
    EmployeeDocument::factory()->forTenant($tenant)->create(['employee_id' => $mine->id]);
    EmployeeDocument::factory()->forTenant($tenant)->create(['employee_id' => $other->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/employee-documents')
        ->assertOk()->assertJsonPath('meta.total', 1);
    $this->actingAs($manager, 'sanctum')->getJson('/api/v1/employee-documents')
        ->assertOk()->assertJsonPath('meta.total', 2);
});

it('deletes the file and the record', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'certificate',
        'title' => 'Degree',
        'file' => UploadedFile::fake()->create('degree.pdf', 40, 'application/pdf'),
    ])->assertCreated();

    $document = EmployeeDocument::withoutGlobalScopes()->firstOrFail();

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/employee-documents/{$document->id}")
        ->assertNoContent();

    Storage::disk('local')->assertMissing($document->path);
    expect(EmployeeDocument::withoutGlobalScopes()->count())->toBe(0);
});

it('404s on another tenant\'s document', function (): void {
    $tenantA = makeTenant(['slug' => 'doc-iso-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'doc-iso-b']);
    $foreign = EmployeeDocument::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->getJson("/api/v1/employee-documents/{$foreign->id}")
        ->assertNotFound();
});
