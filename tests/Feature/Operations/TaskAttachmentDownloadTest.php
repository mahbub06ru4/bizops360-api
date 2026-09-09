<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array<string, mixed>
 */
function uploadAttachment(): array
{
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $url = test()->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [
        'file' => UploadedFile::fake()->create('report.pdf', 25, 'application/pdf'),
    ])->assertCreated()->json('data.download_url');

    return [
        'tenant' => $tenant,
        'manager' => $manager,
        'attachment' => TaskAttachment::withoutGlobalScopes()->firstOrFail(),
        'url' => $url,
    ];
}

it('streams the attachment from a valid signed url', function (): void {
    ['manager' => $manager, 'url' => $url] = uploadAttachment();

    $this->actingAs($manager, 'sanctum')->get($url)
        ->assertOk()
        ->assertDownload('report.pdf');
});

it('rejects an unsigned or tampered file request', function (): void {
    ['manager' => $manager, 'attachment' => $attachment, 'url' => $url] = uploadAttachment();

    $this->actingAs($manager, 'sanctum')
        ->get("/api/v1/task-attachments/{$attachment->id}/file")
        ->assertForbidden();

    $this->actingAs($manager, 'sanctum')->get($url.'x')->assertForbidden();
});

it('forbids an uninvolved staff member from downloading', function (): void {
    ['tenant' => $tenant, 'url' => $url] = uploadAttachment();

    $stranger = makeUser($tenant, 'staff');
    clearTenantContext();

    $this->actingAs($stranger, 'sanctum')->get($url)->assertForbidden();
});
