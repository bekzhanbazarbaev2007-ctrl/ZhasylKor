<div class="page-heading">
    <div><div class="eyebrow">CITY PERFORMANCE <span class="eyebrow-divider">/</span> ENVIRONMENT</div><h1>Analytics</h1><p>Trends and service indicators calculated from reports, tasks, and registered assets.</p></div>
</div>

<section class="report-stat-row">
    <div class="report-stat"><span class="report-stat-dot dot-blue"></span><span>Total reports</span><strong>{{ number_format($reportMetrics['total']) }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-green"></span><span>Completed reports</span><strong>{{ number_format($reportMetrics['completed']) }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-amber"></span><span>Average response time</span><strong>{{ $averageResponseHours }}h</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-red"></span><span>Overdue tasks</span><strong>{{ number_format($taskMetrics['overdue']) }}</strong></div>
</section>

<section class="analytics-grid">
    <article class="panel analytics-trend-panel">
        <div class="panel-heading"><div><h2>Monthly report trends</h2><p>Reports received and reports resolved</p></div></div>
        <div class="chart-canvas-wrap"><canvas id="activity-chart" aria-label="Monthly report trend"></canvas></div>
    </article>
    <article class="panel analytics-category-panel">
        <div class="panel-heading"><div><h2>Reports by category</h2><p>Recorded report categories</p></div></div>
        <div class="chart-canvas-wrap chart-canvas-doughnut"><canvas id="category-chart" aria-label="Reports by category"></canvas></div>
    </article>
</section>

<section class="analytics-grid analytics-grid-bottom">
    <article class="panel">
        <div class="panel-heading"><div><h2>Green asset condition</h2><p>{{ number_format($assetTotal) }} registered assets</p></div></div>
        <div class="chart-canvas-wrap"><canvas id="condition-chart" aria-label="Green asset condition statistics"></canvas></div>
    </article>
    <article class="panel">
        <div class="panel-heading"><div><h2>Task completion</h2><p>Completed versus overdue work</p></div></div>
        <div class="chart-canvas-wrap chart-canvas-doughnut"><canvas id="task-chart" aria-label="Completed versus overdue tasks"></canvas></div>
        <div class="chart-summary"><span><strong>{{ $taskMetrics['completionRate'] }}%</strong> completed</span><span><strong>{{ $taskMetrics['overdue'] }}</strong> overdue</span></div>
    </article>
    <article class="panel analytics-hotspots-panel">
        <div class="panel-heading"><div><h2>Environmental hotspots</h2><p>Locations with the most open reports</p></div></div>
        <div class="hotspot-list">
            @forelse ($hotspots as $hotspot)
                <a class="hotspot-item" href="{{ route('greenfund.screen', ['page' => 'map', 'lat' => $hotspot['lat'], 'lng' => $hotspot['lng']]) }}"><span class="map-report-dot map-report-dot-amber"></span><span>{{ $hotspot['location'] }}</span><strong>{{ $hotspot['count'] }}</strong></a>
            @empty
                <div class="empty-state">Open-report hotspots will appear as reports are recorded.</div>
            @endforelse
        </div>
    </article>
</section>
