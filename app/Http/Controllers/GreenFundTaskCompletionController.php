<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\EnvironmentalTask;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class GreenFundTaskCompletionController extends Controller
{
    public function __invoke(Request $request, EnvironmentalTask $task): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);
        abort_unless(
            $user->role === UserRole::Operator || $task->assigned_to === $user->id,
            403,
        );
        abort_unless(in_array($task->status, ['assigned', 'in_progress'], true), 409);

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $uploadDisk = (string) config('filesystems.public_upload_disk', 'public');
        $photoPath = $request->file('photo')->store('task-completions', $uploadDisk);

        if ($photoPath === false) {
            throw new RuntimeException('The task completion photo could not be stored.');
        }

        DB::transaction(function () use ($task, $validated, $photoPath, $user): void {
            $task->update([
                'status' => 'completed',
                'completion_photo_path' => $photoPath,
                'completion_notes' => $validated['notes'] ?? null,
                'completed_at' => now(),
            ]);

            if ($task->report_id !== null) {
                ReportPhoto::create([
                    'report_id' => $task->report_id,
                    'file_path' => $photoPath,
                    'photo_type' => 'after',
                    'uploaded_by' => $user->id,
                ]);
            }
        });

        return back()->with('success', "Task {$task->task_number} completion submitted for verification.");
    }

    public function updateProgress(Request $request, EnvironmentalTask $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['assigned', 'in_progress'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => [
                'sometimes',
                'nullable',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereIn('role', [
                    UserRole::Executor->value,
                    UserRole::LandscapingSpecialist->value,
                ])),
            ],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ]);

        if ($user->role !== UserRole::Operator) {
            abort_unless(
                $task->assigned_to === $user->id
                && $user->role === UserRole::Executor
                && $validated['status'] === 'in_progress'
                && count($validated) === 1,
                403,
            );
        }

        abort_unless($task->status !== 'completed', 409);

        DB::transaction(function () use ($task, $validated): void {
            $task->update($validated);

            if ($task->report !== null) {
                $task->report->update([
                    'status' => $validated['status'] === 'in_progress' ? 'in_progress' : 'assigned',
                ]);
            }
        });

        return back()->with('success', "Task {$task->task_number} updated.");
    }
}
