<div class="page-heading">
    <div><div class="eyebrow">GREEN INFRASTRUCTURE <span class="eyebrow-divider">/</span> SCHEDULE</div><h1>Maintenance planning</h1><p>Plan watering, inspections, pruning, and cleaning work for the city’s green assets.</p></div>
    @if ($canManageMaintenance)<button class="button button-primary" type="button" data-open-maintenance>Schedule maintenance</button>@endif
</div>

<section class="report-stat-row">
    <div class="report-stat"><span class="report-stat-dot dot-blue"></span><span>Scheduled</span><strong>{{ $maintenanceMetrics['scheduled'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-amber"></span><span>In progress</span><strong>{{ $maintenanceMetrics['inProgress'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-green"></span><span>Completed</span><strong>{{ $maintenanceMetrics['completed'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-red"></span><span>Green assets</span><strong>{{ number_format($assetTotal) }}</strong></div>
</section>

<section class="panel records-panel">
    <form class="records-toolbar" method="GET" action="{{ route('greenfund.screen', ['page' => 'maintenance']) }}">
        <div class="records-actions">
            <label class="table-search"><span class="sr-only">Search maintenance</span><input type="search" name="search" value="{{ $search }}" placeholder="Search schedule..."></label>
            <select class="filter-select" name="status" aria-label="Filter maintenance status"><option value="">All statuses</option><option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option><option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option><option value="completed" @selected(request('status') === 'completed')>Completed</option></select>
            <button class="button button-secondary button-small" type="submit">Filter</button>
        </div>
    </form>
    <div class="schedule-list">
        @forelse ($maintenance as $record)
            <article class="schedule-item">
                <div class="schedule-date"><strong>{{ $record['date'] }}</strong><span>{{ $record['time'] }}</span></div>
                <div class="schedule-details"><span class="eyebrow">{{ $record['kind'] }} · {{ $record['id'] }}</span><h2>{{ $record['title'] }}</h2><p>{{ $record['location'] }} · {{ $record['assignee'] }}</p>@if ($record['description'])<p>{{ $record['description'] }}</p>@endif</div>
                <span class="status status-{{ \Illuminate\Support\Str::slug($record['status']) }}">{{ $record['status'] }}</span>
                @if ($canManageMaintenance && $record['statusKey'] !== 'completed')
                    <form method="POST" action="{{ route('greenfund.maintenance.complete', ['maintenanceRecord' => $record['recordId']]) }}">@csrf @method('PATCH')<button class="button button-secondary button-small" type="submit">Mark complete</button></form>
                @endif
            </article>
        @empty
            <div class="empty-state">No maintenance work matches the selected filters.</div>
        @endforelse
    </div>
    <div class="pagination"><span>Showing <strong>{{ $maintenancePaginator->firstItem() ?? 0 }}–{{ $maintenancePaginator->lastItem() ?? 0 }}</strong> of {{ number_format($maintenancePaginator->total()) }} maintenance records</span><div>{{ $maintenancePaginator->links() }}</div></div>
</section>
