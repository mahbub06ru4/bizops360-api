<?php

declare(strict_types=1);

use App\Modules\HR\Models\EmployeeDocument;
use App\Modules\Organization\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<string, mixed>
 */
function uploadDoc(): array
{
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $employee = Employee::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $url = test()->actingAs($manager, 'sanctum')->post('/api/v1/employee-documents', [
        'employee_id' => $employee->id,
        'category' => 'passport',
        'title' => 'Passport scan',
        'file' => UploadedFile::fake()->create('passport.pdf', 30, 'application/pdf'),
    ])->assertCreated()->json('data.download_url');

    return [
        'manager' => $manager,
        'doc' => EmployeeDocument::withoutGlobalScopes()->firstOrFail(),
        'url' => $url,
    ];
}

it('streams the file from a valid signed url', function (): void {
    ['manager' => $manager, 'url' => $url] = uploadDoc();

    $this->actingAs($manager, 'sanctum')->get($url)
        ->assertOk()
        ->assertDownload('passport.pdf');
});

it('rejects an unsigned request to the file route', function (): void {
    ['manager' => $manager, 'doc' => $doc] = uploadDoc();

    $this->actingAs($manager, 'sanctum')
        ->get("/api/v1/employee-documents/{$doc->id}/file")
        ->assertForbidden();
});

it('rejects a tampered signature', function (): void {
    ['manager' => $manager, 'url' => $url] = uploadDoc();

    $this->actingAs($manager, 'sanctum')->get($url.'x')->assertForbidden();
});

it('forbids a staff member from downloading another employee\'s document', function (): void {
    ['url' => $url, 'doc' => $doc] = uploadDoc();

    $staff = makeUser($doc->tenant, 'staff');
    Employee::factory()->forTenant($doc->tenant)->create(['user_id' => $staff->id]);
    clearTenantContext();

    $this->actingAs($staff, 'sanctum')->get($url)->assertForbidden();
});
