<div class="page-heading heading-dashboard">
    <div>
        <div class="eyebrow"><span class="live-dot"></span> KYZYLORDA CITY · ENVIRONMENTAL OPERATIONS</div>
        <h1>{{ $user ? 'Welcome, '.$user->name : 'City environmental dashboard' }}</h1>
        <p>Current reports, field work, and green infrastructure from the city database.</p>
    </div>
    <a class="button button-primary" href="{{ route('greenfund.screen', ['page' => 'submit']) }}">New report</a>
</div>

<section class="metric-grid" aria-label="Environmental activity overview">
    <article class="metric-card"><div class="metric-top"><span>Total reports</span></div><div class="metric-value">{{ number_format($reportMetrics['total']) }}</div><div class="metric-foot"><span>All recorded reports</span></div></article>
    <article class="metric-card"><div class="metric-top"><span>Open reports</span></div><div class="metric-value">{{ number_format($reportMetrics['open']) }}</div><div class="metric-foot"><span>New or assigned</span></div></article>
    <article class="metric-card"><div class="metric-top"><span>In progress</span></div><div class="metric-value">{{ number_format($reportMetrics['inProgress']) }}</div><div class="metric-foot"><span>Active report work</span></div></article>
    <article class="metric-card"><div class="metric-top"><span>Completed reports</span></div><div class="metric-value">{{ number_format($reportMetrics['completed']) }}</div><div class="metric-foot"><span>Verified as resolved</span></div></article>
    <article class="metric-card"><div class="metric-top"><span>Overdue tasks</span></div><div class="metric-value">{{ number_format($taskMetrics['overdue']) }}</div><div class="metric-foot"><span>Requires attention</span></div></article>
    <article class="metric-card"><div class="metric-top"><span>Registered green assets</span></div><div class="metric-value">{{ number_format($assetTotal) }}</div><div class="metric-foot"><span>Trees, parks, and green areas</span></div></article>
</section>

<section class="dashboard-grid">
    <article class="panel activity-chart-panel">
        <div class="panel-heading"><div><h2>Environmental activity</h2><p>Reports received and resolved by month</p></div></div>
        <div class="chart-canvas-wrap"><canvas id="activity-chart" aria-label="Monthly reports received and resolved"></canvas></div>
        <div class="chart-summary"><span><strong>{{ $reportMetrics['total'] }}</strong> reports recorded</span><span><strong>{{ $reportMetrics['completed'] }}</strong> completed</span></div>
    </article>
    <article class="panel category-panel">
        <div class="panel-heading"><div><h2>Reports by category</h2><p>Distribution of incoming reports</p></div><a class="text-link" href="{{ route('greenfund.screen', ['page' => 'analytics']) }}">Analytics →</a></div>
        <div class="chart-canvas-wrap chart-canvas-doughnut"><canvas id="category-chart" aria-label="Reports by category"></canvas></div>
        <div class="category-legend">
            @foreach ($reportCategoryStats as $category => $count)
                <div><span class="legend-name"><i class="category-color color-{{ $category }}"></i>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $category)) }}</span><strong>{{ $count }}</strong></div>
            @endforeach
        </div>
    </article>
</section>

<section class="dashboard-grid dashboard-grid-bottom">
    <article class="panel recent-reports-panel">
        <div class="panel-heading"><div><h2>Recent reports</h2><p>Latest environmental concerns recorded in Kyzylorda</p></div><a class="button button-secondary button-small" href="{{ route('greenfund.screen', ['page' => 'reports']) }}">View reports →</a></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Report</th><th>Category</th><th>Location</th><th>Submitted</th><th>Priority</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse (array_slice($reports, 0, 5) as $report)
                        <tr>
                            <td><a class="table-id" href="{{ route('greenfund.screen', ['page' => 'reports', 'search' => $report['id']]) }}">{{ $report['id'] }}</a><span class="table-description">{{ $report['description'] }}</span></td>
                            <td>{{ $report['category'] }}</td><td>{{ $report['location'] }}</td><td>{{ $report['date'] }}</td>
                            <td><span class="priority priority-{{ strtolower($report['priority']) }}"><i></i>{{ $report['priority'] }}</span></td>
                            <td><span class="status status-{{ \Illuminate\Support\Str::slug($report['status']) }}">{{ $report['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No reports have been submitted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
    <article class="panel activity-feed-panel">
        <div class="panel-heading"><div><h2>Recent activity</h2><p>Updates recorded by city teams</p></div></div>
        <div class="activity-feed">
            @forelse ($activities as $activity)
                <div class="activity-item"><span class="activity-avatar activity-avatar-{{ $activity['color'] }}">{{ $activity['initials'] }}</span><div><p>{{ $activity['title'] }}</p><span>{{ $activity['detail'] }}</span></div></div>
            @empty
                <div class="empty-state">New reports and completed tasks will appear here.</div>
            @endforelse
        </div>
        <a class="feed-link" href="{{ route('greenfund.screen', ['page' => 'tasks']) }}">View field tasks →</a>
        <div class="task-progress-summary">
            <div class="panel-heading"><div><h2>Task completion</h2><p>{{ $taskMetrics['completed'] }} of {{ $taskMetrics['total'] }} tasks completed</p></div><strong>{{ $taskMetrics['completionRate'] }}%</strong></div>
            <div class="progress-track"><span style="width: {{ $taskMetrics['completionRate'] }}%"></span></div>
        </div>
    </article>
</section>
