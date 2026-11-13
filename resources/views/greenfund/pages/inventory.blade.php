<div class="page-heading">
    <div><div class="eyebrow">CITY ASSETS <span class="eyebrow-divider">/</span> GREEN INFRASTRUCTURE</div><h1>Green inventory</h1><p>Registered trees, parks, squares, and green areas with current condition records.</p></div>
    @if ($canManageAssets)<button class="button button-primary" type="button" data-inventory-add>Register asset</button>@endif
</div>

<section class="report-stat-row inventory-stats">
    <div class="report-stat"><span class="report-stat-dot dot-green"></span><span>Healthy</span><strong>{{ $conditionStats['healthy'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-amber"></span><span>Needs watering</span><strong>{{ $conditionStats['needs_watering'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-red"></span><span>Requires inspection</span><strong>{{ $conditionStats['diseased'] + $conditionStats['drying'] + $conditionStats['pest_damage'] }}</strong></div>
    <div class="report-stat"><span class="report-stat-dot dot-blue"></span><span>Total assets</span><strong>{{ number_format($assetTotal) }}</strong></div>
</section>

<section class="panel records-panel">
    <form class="records-toolbar" method="GET" action="{{ route('greenfund.screen', ['page' => 'inventory']) }}">
        <div class="records-actions">
            <label class="table-search"><span class="sr-only">Search green assets</span><input type="search" name="search" value="{{ $search }}" placeholder="Search assets..."></label>
            <select class="filter-select" name="type" aria-label="Filter assets by type"><option value="">All types</option><option value="tree" @selected(request('type') === 'tree')>Trees</option><option value="park" @selected(request('type') === 'park')>Parks</option><option value="square" @selected(request('type') === 'square')>Squares</option><option value="green_area" @selected(request('type') === 'green_area')>Green areas</option></select>
            <select class="filter-select" name="condition" aria-label="Filter assets by condition"><option value="">All conditions</option><option value="healthy" @selected(request('condition') === 'healthy')>Healthy</option><option value="needs_watering" @selected(request('condition') === 'needs_watering')>Needs watering</option><option value="diseased" @selected(request('condition') === 'diseased')>Diseased</option><option value="drying" @selected(request('condition') === 'drying')>Drying</option><option value="pest_damage" @selected(request('condition') === 'pest_damage')>Pest damage</option></select>
            <button class="button button-secondary button-small" type="submit">Filter</button>
        </div>
    </form>
    <div class="table-wrap">
        <table class="data-table inventory-table">
            <thead><tr><th>Asset</th><th>Type</th><th>Species</th><th>Location</th><th>Condition</th><th>Registered</th><th>Photos</th><th></th></tr></thead>
            <tbody>
                @forelse ($assets as $asset)
                    <tr data-asset-id="{{ $asset['id'] }}">
                        <td><button class="table-id" type="button" data-asset-id="{{ $asset['id'] }}">{{ $asset['id'] }}</button><span class="table-description">{{ $asset['name'] }}</span></td>
                        <td>{{ $asset['type'] }}</td><td>{{ $asset['species'] ?? '—' }}</td><td>{{ $asset['location'] }}</td>
                        <td><span class="status status-{{ \Illuminate\Support\Str::slug($asset['condition']) }}">{{ $asset['condition'] }}</span></td><td>{{ $asset['date'] }}</td><td>{{ count($asset['photos']) }}</td>
                        <td><button class="row-more" type="button" aria-label="View {{ $asset['id'] }}" data-asset-id="{{ $asset['id'] }}">···</button></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No green assets match the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination"><span>Showing <strong>{{ $assetPaginator->firstItem() ?? 0 }}–{{ $assetPaginator->lastItem() ?? 0 }}</strong> of {{ number_format($assetPaginator->total()) }} green assets</span><div>{{ $assetPaginator->links() }}</div></div>
</section>
