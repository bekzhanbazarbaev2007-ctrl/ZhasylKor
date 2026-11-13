<div class="page-heading">
    <div><div class="eyebrow">OPERATIONS <span class="eyebrow-divider">/</span> FIELD WORK</div><h1>{{ $user?->role === \App\Enums\UserRole::Executor ? 'My assigned tasks' : 'Task management' }}</h1><p>Assign, track, and verify environmental work across city teams.</p></div>
    @if ($canManageTasks)<button class="button button-primary" type="button" data-open-task>Create task</button>@endif
</div>

<section class="report-stat-row task-stat-row">
    <div class="report-stat"><span class="report-stat-dot dot-blue"></span><span>Active tasks</span><strong>{{ $taskMetrics['active'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-green"></span><span>Completed this week</span><strong>{{ $taskMetrics['completedThisWeek'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-red"></span><span>Overdue</span><strong>{{ $taskMetrics['overdue'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-amber"></span><span>Unassigned</span><strong>{{ $taskMetrics['unassigned'] }}</strong></div>
</section>

<section class="panel records-panel">
    <form class="records-toolbar" method="GET" action="{{ route('greenfund.screen', ['page' => 'tasks']) }}">
        <div class="records-actions">
            <label class="table-search"><span class="sr-only">Search tasks</span><input type="search" name="search" value="{{ $search }}" placeholder="Search tasks..."></label>
            <select class="filter-select" name="status" aria-label="Filter by task status">
                <option value="">All statuses</option><option value="new" @selected(request('status') === 'new')>New</option>
                <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option><option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option><option value="completed" @selected(request('status') === 'completed')>Completed</option>
            </select>
            <button class="button button-secondary button-small" type="submit">Filter</button>
            <a class="button button-secondary button-small" href="{{ route('greenfund.screen', ['page' => 'maintenance']) }}">Schedule</a>
        </div>
    </form>
    <div class="table-wrap">
        <table class="data-table task-table">
            <thead><tr><th>Task</th><th>Category</th><th>Location</th><th>Assigned employee</th><th>Deadline</th><th>Priority</th><th>Status</th><th></th></tr></thead>
            <tbody id="tasks-table-body">
                @forelse ($tasks as $task)
                    <tr data-task-status="{{ $task['statusKey'] }}" data-search="{{ \Illuminate\Support\Str::lower(implode(' ', [$task['id'], $task['title'], $task['category'], $task['location']])) }}">
                        <td><button class="table-id" type="button" data-task-id="{{ $task['id'] }}">{{ $task['id'] }}</button><span class="table-description">{{ $task['title'] }}</span></td>
                        <td>{{ $task['category'] }}</td><td>{{ $task['location'] }}</td>
                        <td><span class="assignee-cell"><span class="assignee-avatar">{{ $task['assigneeId'] ? \Illuminate\Support\Str::of($task['assignee'])->explode(' ')->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->take(2)->implode('') : '—' }}</span>{{ $task['assignee'] }}</span></td>
                        <td class="{{ $task['status'] === 'Overdue' ? 'text-danger' : '' }}">{{ $task['due'] }}</td>
                        <td><span class="priority priority-{{ strtolower($task['priority']) }}"><i></i>{{ $task['priority'] }}</span></td>
                        <td><span class="status status-{{ \Illuminate\Support\Str::slug($task['status']) }}">{{ $task['status'] }}</span></td>
                        <td><button class="row-more" type="button" aria-label="View task {{ $task['id'] }}" data-task-id="{{ $task['id'] }}">···</button></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No tasks match the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination"><span>Showing <strong>{{ $taskPaginator->firstItem() ?? 0 }}–{{ $taskPaginator->lastItem() ?? 0 }}</strong> of {{ number_format($taskPaginator->total()) }} tasks</span><div>{{ $taskPaginator->links() }}</div></div>
</section>
