<?php

use App\Enums\UserRole;
use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\GreenAsset;
use App\Models\MaintenanceSchedule;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function greenFundTestImage(string $filename): UploadedFile
{
    $imageContents = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=',
        true,
    );

    return UploadedFile::fake()->createWithContent($filename, $imageContents);
}

test('citizens can submit reports with a location and optional photo', function () {
    $uploadDisk = 'greenfund-uploads';
    config(['filesystems.public_upload_disk' => $uploadDisk]);
    Storage::fake($uploadDisk);
    $citizen = User::factory()->create(['role' => UserRole::Citizen]);

    $this->actingAs($citizen);
    Livewire::test('greenfund.report-submission')
        ->set('category', 'illegal_dumping')
        ->set('description', 'Construction waste beside the canal road')
        ->set('latitude', '44.850800')
        ->set('longitude', '65.509300')
        ->set('locationLabel', 'Korkyt Ata Street, near Syr Darya')
        ->set('photo', greenFundTestImage('dumping.png'))
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedCode', fn (string $number): bool => str_starts_with($number, 'ENV-'));

    $report = EnvironmentalReport::query()->firstOrFail();
    $photo = $report->photos()->firstOrFail();

    expect($report->category)->toBe('illegal_dumping')
        ->and($report->status)->toBe('new')
        ->and($report->user_id)->toBe($citizen->id)
        ->and($report->latitude)->toBe(44.8508)
        ->and($report->address)->toBe('Korkyt Ata Street, near Syr Darya')
        ->and($photo->photo_type)->toBe('before');

    Storage::disk($uploadDisk)->assertExists($photo->file_path);
});

test('report submission rejects invalid categories coordinates and descriptions', function () {
    Livewire::test('greenfund.report-submission')
        ->set('category', 'not_a_report_category')
        ->set('description', str_repeat('x', 401))
        ->set('latitude', '95')
        ->set('longitude', '65.5093')
        ->call('submit')
        ->assertHasErrors(['category', 'description', 'latitude']);

    expect(EnvironmentalReport::query()->count())->toBe(0);
});

test('an assigned executor can complete a task and attach an after photo to its report', function () {
    $uploadDisk = 'greenfund-uploads';
    config(['filesystems.public_upload_disk' => $uploadDisk]);
    Storage::fake($uploadDisk);
    $executor = User::factory()->create(['role' => UserRole::Executor]);
    $report = EnvironmentalReport::factory()->create();
    $task = EnvironmentalTask::factory()->for($report, 'report')->create([
        'assigned_to' => $executor->id,
        'status' => 'in_progress',
    ]);

    $this->actingAs($executor)
        ->post(route('greenfund.tasks.complete', ['task' => $task]), [
            'photo' => greenFundTestImage('completed.png'),
            'notes' => 'The waste was removed and the site was cleaned.',
        ])
        ->assertRedirect();

    $task->refresh();
    $afterPhoto = ReportPhoto::query()->where('report_id', $report->id)->firstOrFail();

    expect($task->status)->toBe('completed')
        ->and($task->completion_notes)->toBe('The waste was removed and the site was cleaned.')
        ->and($task->completed_at)->not->toBeNull()
        ->and($afterPhoto->photo_type)->toBe('after');

    Storage::disk($uploadDisk)->assertExists($task->completion_photo_path);
});

test('executors cannot complete another employee task', function () {
    $taskOwner = User::factory()->create(['role' => UserRole::Executor]);
    $otherExecutor = User::factory()->create(['role' => UserRole::Executor]);
    $task = EnvironmentalTask::factory()->create(['assigned_to' => $taskOwner->id]);

    $this->actingAs($otherExecutor)
        ->post(route('greenfund.tasks.complete', ['task' => $task]), [
            'photo' => greenFundTestImage('completed.png'),
        ])
        ->assertForbidden();

    expect($task->fresh()->status)->toBe('assigned');
});

test('operators can assign a report and create a linked field task transactionally', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    $executor = User::factory()->create(['role' => UserRole::Executor]);
    $report = EnvironmentalReport::factory()->create(['status' => 'new']);

    $this->actingAs($operator)
        ->post(route('greenfund.tasks.store'), [
            'report_number' => $report->report_number,
            'title' => 'Clear waste near the canal',
            'category' => 'Waste removal',
            'address' => 'Korkyt Ata Street, near Syr Darya',
            'priority' => 'high',
            'assigned_to' => $executor->id,
            'due_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect(route('greenfund.screen', ['page' => 'tasks']));

    $task = EnvironmentalTask::query()->where('report_id', $report->id)->firstOrFail();

    expect($task->assigned_to)->toBe($executor->id)
        ->and($task->created_by)->toBe($operator->id)
        ->and($task->status)->toBe('assigned')
        ->and($report->fresh()->status)->toBe('assigned')
        ->and($report->fresh()->assigned_to)->toBe($executor->id);
});

test('citizens cannot create operator tasks', function () {
    $citizen = User::factory()->create(['role' => UserRole::Citizen]);

    $this->actingAs($citizen)
        ->post(route('greenfund.tasks.store'), [
            'title' => 'Clear waste',
            'category' => 'Waste removal',
            'address' => 'Korkyt Ata Street',
            'priority' => 'medium',
            'due_date' => now()->addDay()->format('Y-m-d H:i:s'),
        ])
        ->assertForbidden();

    expect(EnvironmentalTask::query()->count())->toBe(0);
});

test('assigned executors can start their own tasks but not change another task', function () {
    $executor = User::factory()->create(['role' => UserRole::Executor]);
    $otherExecutor = User::factory()->create(['role' => UserRole::Executor]);
    $task = EnvironmentalTask::factory()->create(['assigned_to' => $executor->id]);
    $otherTask = EnvironmentalTask::factory()->create(['assigned_to' => $otherExecutor->id]);

    $this->actingAs($executor)
        ->patch(route('greenfund.tasks.progress', ['task' => $task]), ['status' => 'in_progress'])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe('in_progress');

    $this->patch(route('greenfund.tasks.progress', ['task' => $otherTask]), ['status' => 'in_progress'])
        ->assertForbidden();

    expect($otherTask->fresh()->status)->toBe('assigned');
});

test('landscaping specialists can register assets and complete maintenance', function () {
    $specialist = User::factory()->create(['role' => UserRole::LandscapingSpecialist]);

    $this->actingAs($specialist)
        ->post(route('greenfund.assets.store'), [
            'name' => 'Newly planted elm',
            'type' => 'tree',
            'species' => 'Ulmus pumila',
            'condition' => 'needs_watering',
            'address' => 'Arai microdistrict, block 12',
            'latitude' => '44.8338',
            'longitude' => '65.5008',
        ])
        ->assertRedirect(route('greenfund.screen', ['page' => 'inventory']));

    $asset = GreenAsset::query()->firstOrFail();
    $history = $asset->conditionHistory()->firstOrFail();

    expect($history->condition)->toBe('needs_watering')
        ->and($history->updated_by)->toBe($specialist->id);

    $this->post(route('greenfund.maintenance.store'), [
        'title' => 'Water new elm',
        'maintenance_type' => 'watering',
        'location_label' => 'Arai microdistrict, block 12',
        'green_asset_id' => $asset->id,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'description' => 'Water the newly planted tree.',
    ])->assertRedirect(route('greenfund.screen', ['page' => 'maintenance']));

    $record = MaintenanceSchedule::query()->firstOrFail();
    $record->update(['assigned_to' => $specialist->id]);

    $this->patch(route('greenfund.maintenance.complete', ['maintenanceRecord' => $record->id]))
        ->assertRedirect();

    expect($asset->maintenanceSchedules()->count())->toBe(1)
        ->and($record->fresh()->status)->toBe('completed')
        ->and($record->fresh()->completed_at)->not->toBeNull();
});

test('asset condition changes and photos are persisted by authorized specialists', function () {
    $uploadDisk = 'greenfund-uploads';
    config(['filesystems.public_upload_disk' => $uploadDisk]);
    Storage::fake($uploadDisk);
    $specialist = User::factory()->create(['role' => UserRole::LandscapingSpecialist]);
    $asset = GreenAsset::factory()->create(['condition' => 'healthy']);

    $this->actingAs($specialist)
        ->patch(route('greenfund.assets.update', ['greenAsset' => $asset]), [
            'condition' => 'needs_watering',
            'address' => 'Central Park, east entrance',
            'species' => 'Ulmus pumila',
            'description' => 'Recently planted elm.',
            'history_notes' => 'Soil is dry after several hot days.',
        ])
        ->assertRedirect();

    $this->post(route('greenfund.assets.photos.store', ['greenAsset' => $asset]), [
        'photos' => [greenFundTestImage('tree.png')],
    ])->assertRedirect();

    expect($asset->fresh()->condition)->toBe('needs_watering')
        ->and($asset->fresh()->photos)->toHaveCount(1)
        ->and($asset->conditionHistory()->count())->toBe(1)
        ->and($asset->conditionHistory()->first()->updated_by)->toBe($specialist->id);

    Storage::disk($uploadDisk)->assertExists($asset->fresh()->photos[0]);
});

test('citizens cannot update reports or green asset records', function () {
    $citizen = User::factory()->create(['role' => UserRole::Citizen]);
    $report = EnvironmentalReport::factory()->create();
    $asset = GreenAsset::factory()->create();

    $this->actingAs($citizen)
        ->patch(route('greenfund.reports.update', ['report' => $report]), [
            'status' => 'completed',
            'priority' => 'high',
        ])
        ->assertForbidden();

    $this->patch(route('greenfund.assets.update', ['greenAsset' => $asset]), [
        'condition' => 'drying',
        'address' => 'Central Park',
    ])->assertForbidden();
});

test('dashboard statistics and map markers come from persisted records', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    EnvironmentalReport::factory()->create(['status' => 'new', 'category' => 'illegal_dumping']);
    EnvironmentalReport::factory()->create(['status' => 'in_progress', 'category' => 'damaged_tree']);
    EnvironmentalReport::factory()->create(['status' => 'completed', 'category' => 'illegal_dumping']);
    EnvironmentalTask::factory()->create([
        'report_id' => null,
        'status' => 'in_progress',
        'due_date' => now()->subDay(),
    ]);
    GreenAsset::factory()->create(['condition' => 'healthy']);

    $this->actingAs($operator)
        ->get(route('greenfund.screen', ['page' => 'dashboard']))
        ->assertOk()
        ->assertViewHas('reportMetrics', [
            'total' => 3,
            'new' => 1,
            'open' => 1,
            'inProgress' => 1,
            'completed' => 1,
            'highPriority' => 0,
        ])
        ->assertViewHas('taskMetrics', fn (array $metrics): bool => $metrics['overdue'] === 1)
        ->assertViewHas('monthlyTrend', fn (array $trend): bool => $trend['received'][5] === 3)
        ->assertViewHas('assetTotal', 1);

    $this->get(route('greenfund.screen', ['page' => 'map']))
        ->assertOk()
        ->assertViewHas('mapReports', fn (array $reports): bool => count($reports) === 3)
        ->assertViewHas('mapAssets', fn (array $assets): bool => count($assets) === 1);
});
