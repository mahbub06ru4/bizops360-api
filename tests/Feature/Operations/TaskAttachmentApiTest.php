<?php

declare(strict_types=1);

use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskActivity;
use App\Modules\Operations\Models\TaskAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads an attachment, stores the file and records an activity', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [
        'file' => UploadedFile::fake()->create('spec.pdf', 90, 'application/pdf'),
    ])->assertCreated()
        ->assertJsonPath('data.original_name', 'spec.pdf')
        ->assertJsonPath('data.task_id', $task->id)
        ->assertJsonStructure(['data' => ['id', 'download_url']]);

    $attachment = TaskAttachment::withoutGlobalScopes()->firstOrFail();
    expect($attachment->uploaded_by)->toBe($manager->id);
    Storage::disk('local')->assertExists($attachment->path);

    expect(TaskActivity::withoutGlobalScopes()
        ->where('task_id', $task->id)->where('event', 'attachment_added')->count())->toBe(1);
});

it('validates the attachment upload', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [])
        ->assertStatus(422)->assertJsonValidationErrors('file');

    $this->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [
        'file' => UploadedFile::fake()->create('malware.exe', 20, 'application/octet-stream'),
    ])->assertStatus(422)->assertJsonValidationErrors('file');
});

it('deletes the file, the row and records an activity', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $manager = makeUser($tenant, 'manager');
    $task = Task::factory()->forTenant($tenant)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [
        'file' => UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf'),
    ])->assertCreated();

    $attachment = TaskAttachment::withoutGlobalScopes()->firstOrFail();

    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/task-attachments/{$attachment->id}")
        ->assertNoContent();

    Storage::disk('local')->assertMissing($attachment->path);
    expect(TaskAttachment::withoutGlobalScopes()->count())->toBe(0)
        ->and(TaskActivity::withoutGlobalScopes()
            ->where('task_id', $task->id)->where('event', 'attachment_removed')->count())->toBe(1);
});

it('lets the uploader or a manager delete but not an unrelated staff member', function (): void {
    Storage::fake('local');
    $tenant = makeTenant();
    $uploader = makeUser($tenant, 'staff');
    $manager = makeUser($tenant, 'manager');
    $stranger = makeUser($tenant, 'staff');
    $task = Task::factory()->forTenant($tenant)->create(['created_by' => $uploader->id]);
    clearTenantContext();

    $id = $this->actingAs($uploader, 'sanctum')->post("/api/v1/tasks/{$task->id}/attachments", [
        'file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
    ])->assertCreated()->json('data.id');

    $this->actingAs($stranger, 'sanctum')->deleteJson("/api/v1/task-attachments/{$id}")->assertForbidden();
    $this->actingAs($manager, 'sanctum')->deleteJson("/api/v1/task-attachments/{$id}")->assertNoContent();
});

it('hides a task\'s attachments from an uninvolved staff member', function (): void {
    $tenant = makeTenant();
    $outsider = makeUser($tenant, 'staff');
    $task = Task::factory()->forTenant($tenant)->create();
    TaskAttachment::factory()->forTenant($tenant)->create(['task_id' => $task->id]);
    clearTenantContext();

    $this->actingAs($outsider, 'sanctum')->getJson("/api/v1/tasks/{$task->id}/attachments")
        ->assertForbidden();
});

it('404s uploading to another tenant\'s task', function (): void {
    Storage::fake('local');
    $tenantA = makeTenant(['slug' => 'att-up-a']);
    $manager = makeUser($tenantA, 'manager');
    $tenantB = makeTenant(['slug' => 'att-up-b']);
    $foreign = Task::factory()->forTenant($tenantB)->create();
    clearTenantContext();

    $this->actingAs($manager, 'sanctum')->post("/api/v1/tasks/{$foreign->id}/attachments", [
        'file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
    ])->assertNotFound();
});
