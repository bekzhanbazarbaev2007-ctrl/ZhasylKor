<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\GreenAsset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GreenFundController extends Controller
{
    private const PAGES = [
        'dashboard' => 'Dashboard',
        'map' => 'Environmental Map',
        'reports' => 'Reports',
        'submit' => 'Submit a Report',
        'tasks' => 'Tasks',
        'inventory' => 'Green Inventory',
        'maintenance' => 'Maintenance Planning',
        'analytics' => 'Analytics',
        'settings' => 'Settings',
    ];

    private const REPORT_CATEGORIES = [
        'illegal_dumping' => 'Illegal dumping',
        'damaged_tree' => 'Damaged tree',
        'air_pollution' => 'Air pollution',
        'water_pollution' => 'Water pollution',
        'green_space' => 'Green space',
        'other' => 'Other',
    ];

    public function __invoke(Request $request, ?string $page = 'dashboard'): View|RedirectResponse
    {
        abort_unless(array_key_exists($page, self::PAGES), 404);

        $user = $request->user();
        $accessResponse = $this->authorizePage($user, $page);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $navigationPages = $this->navigationPages($user);
        $search = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ])['search'] ?? '';

        $reportPaginator = null;
        if ($page === 'reports') {
            $reportQuery = $this->visibleReports($user);
            $this->applySearch($reportQuery, $search, ['report_number', 'category', 'description', 'address']);

            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                abort_unless(in_array($status, ['new', 'assigned', 'in_progress', 'completed', 'rejected'], true), 422);
                $reportQuery->where('status', $status);
            }

            $sortColumns = [
                'report_number' => 'report_number',
                'created_at' => 'created_at',
                'priority' => 'priority',
                'status' => 'status',
            ];
            $sort = $request->string('sort', 'created_at')->toString();
            $direction = $request->string('direction', 'desc')->toString();
            abort_unless(isset($sortColumns[$sort]) && in_array($direction, ['asc', 'desc'], true), 422);

            $reportPaginator = $reportQuery
                ->orderBy($sortColumns[$sort], $direction)
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
            $reportPaginator->through(fn (EnvironmentalReport $report): array => $this->formatReport($report));
            $reports = $reportPaginator->getCollection()->all();
        } else {
            $reports = $this->visibleReports($user)
                ->latest('created_at')
                ->limit(100)
                ->get()
                ->map(fn (EnvironmentalReport $report): array => $this->formatReport($report))
                ->all();
        }

        $taskPaginator = null;
        if ($page === 'tasks') {
            $taskQuery = $this->visibleTasks($user);
            $this->applySearch($taskQuery, $search, ['task_number', 'title', 'category', 'address']);

            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                if ($status === 'overdue') {
                    $taskQuery->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                        ->where('status', '!=', 'completed');
                } else {
                    abort_unless(in_array($status, ['new', 'assigned', 'in_progress', 'completed'], true), 422);
                    $taskQuery->where('status', $status);
                }
            }

            $taskPaginator = $taskQuery
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
            $taskPaginator->through(fn (EnvironmentalTask $task): array => $this->formatTask($task));
            $tasks = $taskPaginator->getCollection()->all();
        } else {
            $tasks = $this->visibleTasks($user)
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn (EnvironmentalTask $task): array => $this->formatTask($task))
                ->all();
        }

        $assetPaginator = null;
        if ($page === 'inventory') {
            $assetQuery = GreenAsset::query()->with(['conditionHistory.updater', 'maintenanceSchedules.assignee']);
            $this->applySearch($assetQuery, $search, ['asset_number', 'name', 'species', 'address']);
            if ($request->filled('condition')) {
                $condition = $request->string('condition')->toString();
                abort_unless(in_array($condition, ['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage'], true), 422);
                $assetQuery->where('condition', $condition);
            }
            if ($request->filled('type')) {
                $type = $request->string('type')->toString();
                abort_unless(in_array($type, ['tree', 'park', 'square', 'green_area'], true), 422);
                $assetQuery->where('type', $type);
            }

            $assetPaginator = $assetQuery->orderByDesc('registration_date')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();
            $assetPaginator->through(fn (GreenAsset $asset): array => $this->formatAsset($asset));
            $assets = $assetPaginator->getCollection()->all();
        } else {
            $assets = GreenAsset::query()
                ->latest('registration_date')
                ->limit(100)
                ->get()
                ->map(fn (GreenAsset $asset): array => $this->formatAsset($asset))
                ->all();
        }

        $maintenancePaginator = null;
        if ($page === 'maintenance') {
            $maintenanceQuery = MaintenanceSchedule::query()->with(['assignee', 'greenAsset', 'task']);
            $this->applySearch($maintenanceQuery, $search, ['code', 'title', 'maintenance_type', 'location_label']);
            if ($request->filled('status')) {
                $status = $request->string('status')->toString();
                abort_unless(in_array($status, ['scheduled', 'in_progress', 'completed'], true), 422);
                $maintenanceQuery->where('status', $status);
            }

            $maintenancePaginator = $maintenanceQuery
                ->orderBy('scheduled_at')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString();
            $maintenancePaginator->through(fn (MaintenanceSchedule $record): array => $this->formatMaintenance($record));
            $maintenance = $maintenancePaginator->getCollection()->all();
        } else {
            $maintenance = MaintenanceSchedule::query()
                ->with('assignee')
                ->orderBy('scheduled_at')
                ->limit(100)
                ->get()
                ->map(fn (MaintenanceSchedule $record): array => $this->formatMaintenance($record))
                ->all();
        }

        $reportMetrics = $this->reportMetrics($user);
        $taskMetrics = $this->taskMetrics($user);
        $maintenanceMetrics = $this->maintenanceMetrics();
        $assetTotal = GreenAsset::query()->count();
        $reportCategoryStats = $this->reportCategoryStats($user);
        $monthlyTrend = $this->monthlyTrend($user);
        $conditionStats = $this->assetConditionStats();
        $hotspots = $this->hotspots();
        $averageResponseHours = $this->averageResponseHours();

        $mapReports = [];
        $mapAssets = [];
        if ($page === 'map') {
            $mapReports = EnvironmentalReport::query()
                ->with('assignee')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('created_at')
                ->limit(1000)
                ->get()
                ->map(fn (EnvironmentalReport $report): array => $this->formatReport($report))
                ->all();
            $mapAssets = GreenAsset::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->latest('registration_date')
                ->limit(1000)
                ->get()
                ->map(fn (GreenAsset $asset): array => $this->formatAsset($asset))
                ->all();
        }

        $employees = User::query()
            ->whereIn('role', [UserRole::Executor, UserRole::LandscapingSpecialist, UserRole::Operator])
            ->orderBy('name')
            ->get(['id', 'name', 'role'])
            ->map(static fn (User $employee): array => [
                'id' => $employee->id,
                'name' => $employee->name,
                'role' => Str::headline($employee->role->value),
            ])
            ->all();

        $maintenanceAssets = GreenAsset::query()
            ->orderBy('name')
            ->get(['id', 'asset_number', 'name'])
            ->toArray();

        return view('greenfund.app', [
            'page' => $page,
            'pageTitle' => self::PAGES[$page],
            'pages' => self::PAGES,
            'navigationPages' => $navigationPages,
            'showSettings' => $user !== null,
            'user' => $user,
            'roleLabel' => $user ? Str::headline($user->role->value) : 'Citizen',
            'canManageReports' => $user?->role === UserRole::Operator,
            'canManageTasks' => $user?->role === UserRole::Operator,
            'canManageAssets' => in_array($user?->role, [UserRole::Operator, UserRole::LandscapingSpecialist], true),
            'canManageMaintenance' => in_array($user?->role, [UserRole::Operator, UserRole::LandscapingSpecialist], true),
            'canCompleteTasks' => in_array($user?->role, [UserRole::Operator, UserRole::Executor], true),
            'reports' => $reports,
            'tasks' => $tasks,
            'assets' => $assets,
            'maintenance' => $maintenance,
            'reportPaginator' => $reportPaginator,
            'taskPaginator' => $taskPaginator,
            'assetPaginator' => $assetPaginator,
            'maintenancePaginator' => $maintenancePaginator,
            'reportMetrics' => $reportMetrics,
            'taskMetrics' => $taskMetrics,
            'maintenanceMetrics' => $maintenanceMetrics,
            'assetTotal' => $assetTotal,
            'reportCategoryStats' => $reportCategoryStats,
            'monthlyTrend' => $monthlyTrend,
            'conditionStats' => $conditionStats,
            'hotspots' => $hotspots,
            'averageResponseHours' => $averageResponseHours,
            'mapReports' => $mapReports,
            'mapAssets' => $mapAssets,
            'activities' => $page === 'dashboard' ? $this->recentActivities() : [],
            'employees' => $employees,
            'maintenanceAssets' => $maintenanceAssets,
            'search' => $search,
        ]);
    }

    private function authorizePage(?User $user, string $page): ?RedirectResponse
    {
        $rolesByPage = [
            'reports' => [UserRole::Citizen, UserRole::Operator, UserRole::Executor, UserRole::LandscapingSpecialist],
            'tasks' => [UserRole::Operator, UserRole::Executor],
            'inventory' => [UserRole::Operator, UserRole::LandscapingSpecialist],
            'maintenance' => [UserRole::Operator, UserRole::LandscapingSpecialist],
            'analytics' => [UserRole::Operator, UserRole::LandscapingSpecialist],
            'settings' => [UserRole::Citizen, UserRole::Operator, UserRole::Executor, UserRole::LandscapingSpecialist],
        ];

        if (! isset($rolesByPage[$page])) {
            return null;
        }

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        abort_unless(in_array($user->role, $rolesByPage[$page], true), 403);

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function navigationPages(?User $user): array
    {
        return match ($user?->role) {
            UserRole::Operator => [
                'dashboard' => 'Dashboard',
                'map' => 'Environmental Map',
                'reports' => 'Reports',
                'submit' => 'Submit a Report',
                'tasks' => 'Tasks',
                'inventory' => 'Green Inventory',
                'maintenance' => 'Maintenance Planning',
                'analytics' => 'Analytics',
            ],
            UserRole::Citizen => [
                'dashboard' => 'Dashboard',
                'map' => 'Environmental Map',
                'reports' => 'My Reports',
                'submit' => 'Submit a Report',
            ],
            UserRole::Executor => [
                'dashboard' => 'Dashboard',
                'map' => 'Environmental Map',
                'reports' => 'Assigned Reports',
                'tasks' => 'My Tasks',
            ],
            UserRole::LandscapingSpecialist => [
                'dashboard' => 'Dashboard',
                'map' => 'Environmental Map',
                'reports' => 'Reports',
                'inventory' => 'Green Inventory',
                'maintenance' => 'Maintenance Planning',
                'analytics' => 'Analytics',
            ],
            default => [
                'dashboard' => 'Dashboard',
                'map' => 'Environmental Map',
                'submit' => 'Submit a Report',
            ],
        };
    }

    private function visibleReports(?User $user): Builder
    {
        $query = EnvironmentalReport::query()->with(['assignee', 'photos']);

        if ($user?->role === UserRole::Citizen) {
            $query->where('user_id', $user->id);
        } elseif ($user?->role === UserRole::Executor) {
            $query->where('assigned_to', $user->id);
        } elseif ($user?->role === UserRole::LandscapingSpecialist) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    private function visibleTasks(?User $user): Builder
    {
        $query = EnvironmentalTask::query()->with('assignee');

        if ($user?->role === UserRole::Executor) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function applySearch(Builder $query, string $search, array $columns): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($columns, $search): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', '%'.$search.'%');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function formatReport(EnvironmentalReport $report): array
    {
        return [
            'id' => $report->report_number,
            'category' => self::REPORT_CATEGORIES[$report->category] ?? Str::headline($report->category),
            'categoryKey' => $report->category,
            'description' => $report->description,
            'additionalInformation' => $report->additional_information,
            'location' => $report->address ?? 'Kyzylorda city',
            'date' => $report->created_at?->format('M d, H:i') ?? '—',
            'dateIso' => $report->created_at?->toIso8601String(),
            'priority' => Str::headline($report->priority),
            'status' => Str::headline($report->status),
            'statusKey' => $report->status,
            'assignee' => $report->assignee?->name ?? 'Unassigned',
            'assigneeId' => $report->assigned_to,
            'lat' => $report->latitude,
            'lng' => $report->longitude,
            'photos' => $report->relationLoaded('photos')
                ? $report->photos->map(fn ($photo): array => [
                    'url' => Storage::disk((string) config('filesystems.public_upload_disk', 'public'))
                        ->url($photo->file_path),
                    'type' => $photo->photo_type,
                ])->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatTask(EnvironmentalTask $task): array
    {
        $overdue = $task->status !== 'completed' && $task->due_date?->isPast();

        return [
            'recordId' => $task->id,
            'id' => $task->task_number,
            'title' => $task->title,
            'description' => $task->description,
            'category' => Str::headline($task->category),
            'location' => $task->address,
            'assignee' => $task->assignee?->name ?? 'Unassigned',
            'assigneeId' => $task->assigned_to,
            'due' => $task->due_date?->format('M d, H:i') ?? 'No deadline',
            'dueIso' => $task->due_date?->toIso8601String(),
            'priority' => Str::headline($task->priority),
            'status' => $overdue ? 'Overdue' : Str::headline($task->status),
            'statusKey' => $task->status,
            'completionPhoto' => $task->completion_photo_path
                ? Storage::disk((string) config('filesystems.public_upload_disk', 'public'))
                    ->url($task->completion_photo_path)
                : null,
            'lat' => $task->latitude ?? 44.8528,
            'lng' => $task->longitude ?? 65.5092,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAsset(GreenAsset $asset): array
    {
        return [
            'name' => $asset->name,
            'species' => $asset->species,
            'description' => $asset->description,
            'type' => Str::headline($asset->type),
            'typeKey' => $asset->type,
            'id' => $asset->asset_number,
            'location' => $asset->address,
            'condition' => Str::headline(str_replace('_', ' ', $asset->condition)),
            'conditionKey' => $asset->condition,
            'date' => $asset->registration_date?->format('M d, Y') ?? '—',
            'icon' => $asset->type === 'tree' ? 'tree' : 'park',
            'lat' => $asset->latitude,
            'lng' => $asset->longitude,
            'photos' => collect($asset->photos ?? [])
                ->map(fn (string $path): string => Storage::disk(
                    (string) config('filesystems.public_upload_disk', 'public'),
                )->url($path))
                ->all(),
            'history' => $asset->relationLoaded('conditionHistory')
                ? $asset->conditionHistory->map(static fn ($history): array => [
                    'condition' => Str::headline(str_replace('_', ' ', $history->condition)),
                    'notes' => $history->notes,
                    'date' => $history->created_at->format('M d, Y'),
                    'updatedBy' => $history->updater?->name ?? 'City team',
                ])->all()
                : [],
            'maintenance' => $asset->relationLoaded('maintenanceSchedules')
                ? $asset->maintenanceSchedules->map(static fn (MaintenanceSchedule $record): array => [
                    'title' => $record->title,
                    'date' => $record->scheduled_at->format('M d, Y'),
                    'status' => Str::headline($record->status),
                    'assignee' => $record->assignee?->name ?? 'Unassigned',
                ])->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMaintenance(MaintenanceSchedule $record): array
    {
        return [
            'recordId' => $record->id,
            'id' => $record->code,
            'title' => $record->title,
            'description' => $record->description,
            'date' => $record->scheduled_at->format('M d'),
            'time' => $record->scheduled_at->format('H:i'),
            'location' => $record->location_label,
            'assignee' => $record->assignee?->name ?? 'Unassigned',
            'assignedTo' => $record->assigned_to,
            'status' => Str::headline($record->status),
            'statusKey' => $record->status,
            'kind' => Str::headline($record->maintenance_type),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function reportMetrics(?User $user): array
    {
        $query = $this->visibleReports($user);

        return [
            'total' => (clone $query)->count(),
            'new' => (clone $query)->where('status', 'new')->count(),
            'open' => (clone $query)->whereIn('status', ['new', 'assigned'])->count(),
            'inProgress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'highPriority' => (clone $query)->whereIn('priority', ['high', 'urgent'])->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function taskMetrics(?User $user): array
    {
        $query = $this->visibleTasks($user);

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();

        return [
            'total' => $total,
            'active' => (clone $query)->whereIn('status', ['new', 'assigned', 'in_progress'])->count(),
            'completed' => $completed,
            'completedThisWeek' => (clone $query)->where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfWeek())->count(),
            'overdue' => (clone $query)->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')->count(),
            'unassigned' => (clone $query)->whereNull('assigned_to')
                ->where('status', '!=', 'completed')->count(),
            'completionRate' => $total === 0 ? 0 : round(($completed / $total) * 100, 1),
        ];
    }

    /**
     * @return array{scheduled: int, inProgress: int, completed: int}
     */
    private function maintenanceMetrics(): array
    {
        return [
            'scheduled' => MaintenanceSchedule::query()->where('status', 'scheduled')->count(),
            'inProgress' => MaintenanceSchedule::query()->where('status', 'in_progress')->count(),
            'completed' => MaintenanceSchedule::query()->where('status', 'completed')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function reportCategoryStats(?User $user): array
    {
        $query = $this->visibleReports($user);
        $counts = (clone $query)->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return collect(self::REPORT_CATEGORIES)
            ->mapWithKeys(fn (string $label, string $category): array => [$category => (int) ($counts[$category] ?? 0)])
            ->all();
    }

    /**
     * @return array{labels: array<int, string>, received: array<int, int>, resolved: array<int, int>}
     */
    private function monthlyTrend(?User $user): array
    {
        $start = now()->subMonths(5)->startOfMonth();
        $reports = $this->visibleReports($user);
        $receivedCounts = (clone $reports)
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->countBy(fn (EnvironmentalReport $report): string => $report->created_at->format('Y-m'));
        $resolvedCounts = (clone $reports)
            ->where('status', 'completed')
            ->where('updated_at', '>=', $start)
            ->get(['updated_at'])
            ->countBy(fn (EnvironmentalReport $report): string => $report->updated_at->format('Y-m'));

        $months = collect(range(0, 5))
            ->map(fn (int $offset): CarbonImmutable => now()->subMonths(5 - $offset)->startOfMonth());

        return [
            'labels' => $months->map(fn (CarbonImmutable $month): string => $month->format('M'))->all(),
            'received' => $months->map(fn (CarbonImmutable $month): int => (int) ($receivedCounts[$month->format('Y-m')] ?? 0))->all(),
            'resolved' => $months->map(fn (CarbonImmutable $month): int => (int) ($resolvedCounts[$month->format('Y-m')] ?? 0))->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function assetConditionStats(): array
    {
        $counts = GreenAsset::query()->selectRaw('condition, COUNT(*) as total')
            ->groupBy('condition')
            ->pluck('total', 'condition');

        return collect(['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage'])
            ->mapWithKeys(fn (string $condition): array => [$condition => (int) ($counts[$condition] ?? 0)])
            ->all();
    }

    /**
     * @return array<int, array{location: string, count: int, lat: float|null, lng: float|null}>
     */
    private function hotspots(): array
    {
        return EnvironmentalReport::query()
            ->whereNotIn('status', ['completed', 'rejected'])
            ->selectRaw('address as location, COUNT(*) as total, AVG(latitude) as lat, AVG(longitude) as lng')
            ->groupBy('address')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(static fn ($hotspot): array => [
                'location' => $hotspot->location ?? 'Kyzylorda city',
                'count' => (int) $hotspot->total,
                'lat' => $hotspot->lat === null ? null : (float) $hotspot->lat,
                'lng' => $hotspot->lng === null ? null : (float) $hotspot->lng,
            ])
            ->all();
    }

    private function averageResponseHours(): float
    {
        $average = DB::table('tasks')
            ->join('reports', 'tasks.report_id', '=', 'reports.id')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG((julianday(tasks.completed_at) - julianday(reports.created_at)) * 24) as average_hours')
            ->value('average_hours');

        return $average === null ? 0 : round((float) $average, 1);
    }

    /**
     * @return array<int, array{initials: string, color: string, title: string, detail: string, type: string, timestamp: Carbon}>
     */
    private function recentActivities(): array
    {
        $reports = EnvironmentalReport::query()
            ->with('user')
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(static fn (EnvironmentalReport $report): array => [
                'initials' => $report->user
                    ? Str::of($report->user->name)->explode(' ')->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))->take(2)->implode('')
                    : 'GF',
                'color' => 'blue',
                'title' => 'New '.(self::REPORT_CATEGORIES[$report->category] ?? Str::headline($report->category)).' report received',
                'detail' => $report->report_number.' · '.($report->address ?? 'Kyzylorda city').' · '.$report->created_at->diffForHumans(),
                'type' => 'New report',
                'timestamp' => $report->created_at,
            ]);

        $tasks = EnvironmentalTask::query()
            ->with('assignee')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(3)
            ->get()
            ->map(static fn (EnvironmentalTask $task): array => [
                'initials' => $task->assignee
                    ? Str::of($task->assignee->name)->explode(' ')->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))->take(2)->implode('')
                    : 'GF',
                'color' => 'green',
                'title' => 'Field task completed',
                'detail' => $task->task_number.' · '.$task->title.' · '.$task->completed_at->diffForHumans(),
                'type' => 'Completed',
                'timestamp' => $task->completed_at,
            ]);

        return $reports->concat($tasks)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values()
            ->all();
    }
}
