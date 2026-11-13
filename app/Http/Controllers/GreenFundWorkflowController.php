<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\GreenAsset;
use App\Models\MaintenanceSchedule;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class GreenFundWorkflowController extends Controller
{
    public function updateReport(Request $request, EnvironmentalReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'assigned', 'in_progress', 'completed', 'rejected'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereIn('role', [
                    UserRole::Executor->value,
                    UserRole::LandscapingSpecialist->value,
                    UserRole::Operator->value,
                ])),
            ],
        ]);

        $report->update($validated);

        return back()->with('success', "Report {$report->report_number} updated.");
    }

    public function storeTask(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_number' => ['nullable', 'string', 'exists:reports,report_number'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Waste removal', 'Tree inspection', 'Watering', 'Irrigation', 'Cleaning', 'Pruning', 'Other'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereIn('role', [
                    UserRole::Executor->value,
                    UserRole::LandscapingSpecialist->value,
                ])),
            ],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $report = isset($validated['report_number'])
            ? EnvironmentalReport::query()->where('report_number', $validated['report_number'])->firstOrFail()
            : null;
        $taskNumber = $this->uniqueNumber('TSK-', 'task_number', 'tasks');

        DB::transaction(function () use ($validated, $report, $taskNumber, $request): void {
            EnvironmentalTask::create([
                'task_number' => $taskNumber,
                'report_id' => $report?->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? $report?->latitude,
                'longitude' => $validated['longitude'] ?? $report?->longitude,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => $request->user()->id,
                'priority' => $validated['priority'],
                'status' => isset($validated['assigned_to']) ? 'assigned' : 'new',
                'due_date' => $validated['due_date'],
            ]);

            if ($report) {
                $report->update([
                    'status' => isset($validated['assigned_to']) ? 'assigned' : $report->status,
                    'assigned_to' => $validated['assigned_to'] ?? null,
                ]);
            }
        });

        return to_route('greenfund.screen', ['page' => 'tasks'])
            ->with('success', "Task {$taskNumber} created.");
    }

    public function storeAsset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['tree', 'park', 'square', 'green_area'])],
            'species' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'condition' => ['required', Rule::in(['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage'])],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $prefix = $validated['type'] === 'tree' ? 'TREE-' : 'AREA-';
        $assetNumber = $this->uniqueNumber($prefix, 'asset_number', 'green_assets');

        DB::transaction(function () use ($validated, $assetNumber, $request): void {
            $asset = GreenAsset::create([
                ...$validated,
                'asset_number' => $assetNumber,
                'registration_date' => now()->toDateString(),
            ]);

            $asset->conditionHistory()->create([
                'condition' => $asset->condition,
                'notes' => 'Initial condition at registration.',
                'updated_by' => $request->user()->id,
            ]);
        });

        return to_route('greenfund.screen', ['page' => 'inventory'])
            ->with('success', "Green asset {$assetNumber} registered.");
    }

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'maintenance_type' => ['required', Rule::in(['watering', 'inspection', 'pruning', 'cleaning', 'other'])],
            'location_label' => ['required', 'string', 'max:255'],
            'green_asset_id' => ['nullable', 'exists:green_assets,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereIn('role', [
                    UserRole::Executor->value,
                    UserRole::LandscapingSpecialist->value,
                ])),
            ],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $code = $this->uniqueNumber('MNT-', 'code', 'maintenance_records');

        MaintenanceSchedule::create([
            ...$validated,
            'code' => $code,
            'status' => 'scheduled',
        ]);

        return to_route('greenfund.screen', ['page' => 'maintenance'])
            ->with('success', 'Maintenance work scheduled.');
    }

    public function updateAsset(Request $request, GreenAsset $greenAsset): RedirectResponse
    {
        $validated = $request->validate([
            'condition' => ['required', Rule::in(['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage'])],
            'address' => ['required', 'string', 'max:255'],
            'species' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'history_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($greenAsset, $validated, $request): void {
            $conditionChanged = $greenAsset->condition !== $validated['condition'];
            $greenAsset->update([
                'condition' => $validated['condition'],
                'address' => $validated['address'],
                'species' => $validated['species'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            if ($conditionChanged) {
                $greenAsset->conditionHistory()->create([
                    'condition' => $validated['condition'],
                    'notes' => $validated['history_notes'] ?? null,
                    'updated_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', 'Green asset profile updated.');
    }

    public function storeAssetPhotos(Request $request, GreenAsset $greenAsset): RedirectResponse
    {
        $validated = $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $photoPaths = [];
        $uploadDisk = (string) config('filesystems.public_upload_disk', 'public');
        foreach ($validated['photos'] as $photo) {
            $path = $photo->store('green-assets', $uploadDisk);

            if ($path === false) {
                throw new RuntimeException('A green asset photo could not be stored.');
            }

            $photoPaths[] = $path;
        }

        $greenAsset->update([
            'photos' => [...($greenAsset->photos ?? []), ...$photoPaths],
        ]);

        return back()->with('success', 'Green asset photos uploaded.');
    }

    public function completeMaintenance(Request $request, MaintenanceSchedule $maintenanceRecord): RedirectResponse
    {
        $user = $request->user();
        abort_unless(
            $user->role === UserRole::Operator
            || ($user->role === UserRole::LandscapingSpecialist && $maintenanceRecord->assigned_to === $user->id),
            403,
        );
        abort_unless($maintenanceRecord->status !== 'completed', 409);

        $maintenanceRecord->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Maintenance record marked as completed.');
    }

    private function uniqueNumber(string $prefix, string $column, string $table): string
    {
        do {
            $number = $prefix.Str::upper(Str::random(8));
        } while (DB::table($table)->where($column, $number)->exists());

        return $number;
    }
}
