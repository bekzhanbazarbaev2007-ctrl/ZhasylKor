<div class="page-heading">
    <div><div class="eyebrow">CITYWIDE VIEW <span class="eyebrow-divider">/</span> KYZYLORDA</div><h1>Environmental Map</h1><p>Explore database-backed reports and registered green assets across the city.</p></div>
    <a class="button button-primary" href="{{ route('greenfund.screen', ['page' => 'submit']) }}">New report</a>
</div>

<section class="map-toolbar panel">
    <label class="map-search"><span class="sr-only">Search map locations</span><input id="map-search" type="search" placeholder="Search a street, area, or report" aria-label="Search map locations"></label>
    <span class="toolbar-divider"></span>
    <label class="filter-label" for="map-category">Category</label>
    <select class="filter-select" id="map-category">
        <option value="">All categories</option>
        <option value="illegal_dumping">Illegal dumping</option><option value="damaged_tree">Damaged tree</option>
        <option value="air_pollution">Air pollution</option><option value="water_pollution">Water pollution</option>
        <option value="green_space">Green space</option><option value="other">Other</option><option value="green_assets">Green assets</option>
    </select>
    <label class="filter-label filter-status-label" for="map-status">Status</label>
    <select class="filter-select" id="map-status"><option value="">All statuses</option><option value="new">New</option><option value="assigned">Assigned</option><option value="in_progress">In Progress</option><option value="completed">Completed</option><option value="rejected">Rejected</option></select>
    <button class="button button-quiet" id="map-reset" type="button">Reset</button>
</section>

<section class="map-layout">
    <div class="map-stage">
        <div id="environment-map" class="environment-map" role="application" aria-label="Interactive map of Kyzylorda environmental reports and green assets"></div>
        <div class="map-overlay map-top-left"><span class="map-live-dot"></span> Database records <span class="map-overlay-divider"></span> {{ count($mapReports) }} reports · {{ count($mapAssets) }} assets</div>
        <button class="map-locate" id="map-locate" type="button" aria-label="Return to Kyzylorda city center">⌖</button>
        <div class="map-legend"><strong>MAP LEGEND</strong><span><i class="map-legend-dot legend-waste"></i>Waste</span><span><i class="map-legend-dot legend-tree"></i>Greenery</span><span><i class="map-legend-dot legend-pollution"></i>Pollution</span><span><i class="map-legend-dot legend-park"></i>Green assets</span></div>
        <div class="map-attribution">Map data © OpenStreetMap contributors</div>
    </div>
    <aside class="map-side panel">
        <div class="map-side-heading"><div><h2>City overview</h2><p>Records with mapped coordinates</p></div><span class="map-side-count" id="map-result-count">{{ count($mapReports) + count($mapAssets) }}</span></div>
        <div class="map-summary">
            <div><span class="map-summary-dot summary-dot-amber"></span><span>Open reports</span><strong>{{ $reportMetrics['open'] }}</strong></div>
            <div><span class="map-summary-dot summary-dot-green"></span><span>Green assets</span><strong>{{ number_format($assetTotal) }}</strong></div>
            <div><span class="map-summary-dot summary-dot-red"></span><span>High priority</span><strong>{{ $reportMetrics['highPriority'] }}</strong></div>
        </div>
        <div class="map-results-title">RECENT REPORTS <span id="map-nearby-total">{{ count($mapReports) }} reports</span></div>
        <div class="map-results" id="map-results">
            @forelse (array_slice($mapReports, 0, 12) as $report)
                <button class="map-report-card" type="button" data-report-id="{{ $report['id'] }}">
                    <span class="map-report-dot {{ in_array($report['categoryKey'], ['damaged_tree', 'green_space'], true) ? 'map-report-dot-green' : (in_array($report['categoryKey'], ['air_pollution', 'water_pollution'], true) ? 'map-report-dot-red' : 'map-report-dot-amber') }}"></span>
                    <span class="map-report-info"><strong>{{ $report['category'] }} <small>{{ $report['id'] }}</small></strong><span>{{ $report['location'] }}</span><span class="map-report-meta">{{ $report['date'] }} <i>·</i> {{ $report['status'] }}</span></span>
                    <svg class="map-report-arrow" viewBox="0 0 16 16" aria-hidden="true"><path d="m6 3 5 5-5 5"/></svg>
                </button>
            @empty
                <div class="empty-state">No mapped reports are available.</div>
            @endforelse
        </div>
        <a class="map-all-reports" href="{{ route('greenfund.screen', ['page' => 'reports']) }}">Open report list →</a>
    </aside>
</section>
