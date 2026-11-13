<div class="page-heading">
    <div><div class="eyebrow">OPERATIONS <span class="eyebrow-divider">/</span> RESIDENT SUBMISSIONS</div><h1>{{ $user?->role === \App\Enums\UserRole::Citizen ? 'My environmental reports' : 'Environmental reports' }}</h1><p>Review reports, assign city teams, and follow work through resolution.</p></div>
    <a class="button button-primary" href="{{ route('greenfund.screen', ['page' => 'submit']) }}">New report</a>
</div>

<section class="report-stat-row">
    <div class="report-stat"><span class="report-stat-dot dot-amber"></span><span>Open reports</span><strong>{{ $reportMetrics['open'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-blue"></span><span>In progress</span><strong>{{ $reportMetrics['inProgress'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-green"></span><span>Completed</span><strong>{{ $reportMetrics['completed'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-red"></span><span>High priority</span><strong>{{ $reportMetrics['highPriority'] }}</strong></div>
</section>

<section class="panel records-panel">
    <form class="records-toolbar" method="GET" action="{{ route('greenfund.screen', ['page' => 'reports']) }}">
        <div class="records-actions">
            <label class="table-search"><span class="sr-only">Search reports</span><input id="records-search" type="search" name="search" value="{{ $search }}" placeholder="Search reports..."></label>
            <select class="filter-select" name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['new' => 'New', 'assigned' => 'Assigned', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="button button-secondary button-small" type="submit">Filter</button>
        </div>
    </form>
    <div class="table-wrap">
        <table class="data-table reports-table">
            <thead><tr>
                <th><a class="sort-button" href="{{ route('greenfund.screen', ['page' => 'reports', 'search' => $search, 'status' => request('status'), 'sort' => 'report_number', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">Report ID ↕</a></th>
                <th>Category</th><th>Description</th><th>Location</th>
                <th><a class="sort-button" href="{{ route('greenfund.screen', ['page' => 'reports', 'search' => $search, 'status' => request('status'), 'sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">Submitted ↕</a></th>
                <th>Priority</th><th>Status</th><th>Assigned to</th><th></th>
            </tr></thead>
            <tbody id="reports-table-body">
                @forelse ($reports as $report)
                    <tr data-status="{{ $report['statusKey'] }}" data-search="{{ \Illuminate\Support\Str::lower(implode(' ', [$report['id'], $report['category'], $report['description'], $report['location'], $report['assignee']])) }}">
                        <td><button class="table-id" type="button" data-open-detail="{{ $report['id'] }}">{{ $report['id'] }}</button></td>
                        <td>{{ $report['category'] }}</td><td class="description-cell">{{ $report['description'] }}</td><td>{{ $report['location'] }}</td><td>{{ $report['date'] }}</td>
                        <td><span class="priority priority-{{ strtolower($report['priority']) }}"><i></i>{{ $report['priority'] }}</span></td>
                        <td><span class="status status-{{ \Illuminate\Support\Str::slug($report['status']) }}">{{ $report['status'] }}</span></td>
                        <td><span class="assignee-cell"><span class="assignee-avatar">{{ $report['assigneeId'] ? \Illuminate\Support\Str::of($report['assignee'])->explode(' ')->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->take(2)->implode('') : '—' }}</span>{{ $report['assignee'] }}</span></td>
                        <td><button class="row-more" aria-label="Open {{ $report['id'] }} details" type="button" data-open-detail="{{ $report['id'] }}">···</button></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-state">No reports match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination"><span>Showing <strong>{{ $reportPaginator->firstItem() ?? 0 }}–{{ $reportPaginator->lastItem() ?? 0 }}</strong> of {{ number_format($reportPaginator->total()) }} reports</span><div>{{ $reportPaginator->links() }}</div></div>
</section>
