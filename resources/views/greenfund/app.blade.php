<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} · ЖасылҚор</title>
    <link rel="stylesheet" href="{{ asset('greenfund.css') }}">
    @livewireStyles
    @if (in_array($page, ['map', 'submit'], true))
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endif
    @if ($page === 'map')
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    @endif
    @vite(['resources/js/app.js'])
</head>
<body class="greenfund">
    <div class="app-shell">
        <aside class="sidebar" id="app-sidebar">
            <a class="brand" href="{{ route('greenfund.screen', ['page' => 'dashboard']) }}">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M25.8 5.8C15.2 6 8.1 9.2 7.7 16.4c-.2 3.2 2.1 5.4 5.3 5.2 7.4-.4 10.1-7.9 12.8-15.8Z" fill="currentColor"/><path d="M6.2 27.1c2.4-6.1 6.6-10.1 12.7-13.2" stroke="white" stroke-width="2" stroke-linecap="round"/></svg></span>
                <span class="brand-copy"><strong>ЖасылҚор</strong><small>GREENFUND MAP</small></span>
            </a>

            <div class="workspace-switcher">
                <span class="workspace-emblem">KZ</span>
                <span><strong>Kyzylorda city</strong><small>Municipal workspace</small></span>
                <svg class="switch-chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="m4 6 4 4 4-4"/></svg>
            </div>

            <div class="nav-label">WORKSPACE</div>
            <nav class="primary-nav" aria-label="Main navigation">
                @foreach ($navigationPages as $key => $label)
                    <a class="nav-link {{ $page === $key ? 'is-active' : '' }}" href="{{ route('greenfund.screen', ['page' => $key]) }}" @if ($page === $key) aria-current="page" @endif>
                        <span class="nav-icon" aria-hidden="true">
                            @switch($key)
                                @case('dashboard') <svg viewBox="0 0 20 20"><rect x="3" y="3" width="5.5" height="5.5" rx="1"/><rect x="11.5" y="3" width="5.5" height="3.5" rx="1"/><rect x="11.5" y="8.5" width="5.5" height="8.5" rx="1"/><rect x="3" y="10.5" width="5.5" height="6.5" rx="1"/></svg> @break
                                @case('map') <svg viewBox="0 0 20 20"><path d="M10 17s5-4.2 5-9a5 5 0 0 0-10 0c0 4.8 5 9 5 9Z"/><circle cx="10" cy="8" r="1.7"/></svg> @break
                                @case('reports') <svg viewBox="0 0 20 20"><path d="M6 2.8h5.5L16 7.3v9.2a.7.7 0 0 1-.7.7H6a2 2 0 0 1-2-2V4.8a2 2 0 0 1 2-2Z"/><path d="M11 3v4.5h4.5M7.5 11h5M7.5 14h5"/></svg> @break
                                @case('submit') <svg viewBox="0 0 20 20"><path d="M10 4v12M4 10h12"/></svg> @break
                                @case('tasks') <svg viewBox="0 0 20 20"><path d="m4 10 3.5 3.5L16 5"/><path d="M16 10v5.5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h7"/></svg> @break
                                @case('inventory') <svg viewBox="0 0 20 20"><path d="M10 17V8m0 4c-3.3 0-5.5-2-5.5-5.2C7.8 6.8 10 8.7 10 12Zm0-2.5c0-3.4 2.2-5.4 5.5-5.4C15.5 7.5 13.3 9.5 10 9.5Z"/><path d="M6.5 17h7"/></svg> @break
                                @case('maintenance') <svg viewBox="0 0 20 20"><rect x="3" y="4.5" width="14" height="12" rx="1.5"/><path d="M6.5 2.8v3.4M13.5 2.8v3.4M3 8h14M6.5 11h2M11.5 11h2M6.5 14h2"/></svg> @break
                                @case('analytics') <svg viewBox="0 0 20 20"><path d="M3 16.5h14"/><rect x="4" y="9" width="2.7" height="5.5" rx=".7"/><rect x="8.7" y="5.5" width="2.7" height="9" rx=".7"/><rect x="13.4" y="3" width="2.7" height="11.5" rx=".7"/></svg> @break
                            @endswitch
                        </span>
                        <span>{{ $label }}</span>
                        @if ($key === 'reports' && $reportMetrics['open'] > 0)<span class="nav-count">{{ $reportMetrics['open'] }}</span>@endif
                        @if ($key === 'tasks' && $taskMetrics['overdue'] > 0)<span class="nav-count nav-count-alert">{{ $taskMetrics['overdue'] }}</span>@endif
                    </a>
                @endforeach
            </nav>

            @if ($showSettings)
                <div class="nav-label nav-label-bottom">PREFERENCES</div>
                <a class="nav-link {{ $page === 'settings' ? 'is-active' : '' }}" href="{{ route('greenfund.screen', ['page' => 'settings']) }}" @if ($page === 'settings') aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/><path d="m16 11.5 1 .8-1 1.8-1.3-.4a6.7 6.7 0 0 1-1.5.9l-.3 1.4h-2l-.4-1.3a6.7 6.7 0 0 1-1.7-.5l-1.2.7-1.4-1.4.7-1.2a6.7 6.7 0 0 1-.5-1.7l-1.3-.4v-2l1.4-.3a6.7 6.7 0 0 1 .9-1.5l-.4-1.3 1.8-1 1 .9a6.7 6.7 0 0 1 1.7-.1l.8-1.1 1.9.7-.1 1.4a6.7 6.7 0 0 1 1.1 1.3l1.4-.1.7 1.9-1.1.8a6.7 6.7 0 0 1 .1 1.7Z"/></svg></span>
                    <span>Settings</span>
                </a>
            @endif

            <div class="sidebar-spacer"></div>
            <div class="support-card">
                <span class="support-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="7"/><path d="M8 8a2.1 2.1 0 1 1 3.7 1.4c-.8.8-1.7 1.1-1.7 2.4M10 14.5h.01"/></svg></span>
                <strong>Need a hand?</strong>
                <span>Our city operations team is here to help.</span>
                <a href="mailto:greenfund@kyzylorda.kz">Contact support <span aria-hidden="true">→</span></a>
            </div>
            @auth
                <a class="user-card" href="{{ route('profile.edit') }}">
                    <span class="avatar avatar-sidebar">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->take(2)->implode('') }}</span>
                    <span class="user-meta"><strong>{{ $user->name }}</strong><small>{{ $roleLabel }}</small></span>
                    <svg class="user-more" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1"/><circle cx="10" cy="10" r="1"/><circle cx="16" cy="10" r="1"/></svg>
                </a>
            @else
                <a class="user-card" href="{{ route('login') }}"><span class="avatar avatar-sidebar">GU</span><span class="user-meta"><strong>Guest</strong><small>Sign in to track reports</small></span></a>
            @endauth
        </aside>

        <div class="main-shell">
            <header class="topbar">
                <button class="mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="app-sidebar" id="mobile-menu-button"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14"/></svg></button>
                <div class="breadcrumb"><span>Workspace</span><span class="breadcrumb-separator">/</span><strong>{{ $pageTitle }}</strong></div>
                <div class="topbar-actions">
                    <form class="global-search" method="GET" action="{{ route('greenfund.screen', ['page' => $page]) }}">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.8" cy="8.8" r="5.5"/><path d="m13 13 4 4"/></svg>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Search reports, tasks..." aria-label="Search current page">
                        <button class="search-submit" type="submit">Search</button>
                    </form>
                    <button class="icon-button notification-button" type="button" aria-label="{{ $reportMetrics['new'] }} new reports">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M15.5 8a5.5 5.5 0 0 0-11 0c0 6-2 6-2 7.5h15C17.5 14 15.5 14 15.5 8ZM8 18h4"/></svg>
                        @if ($reportMetrics['new'] > 0)<span></span>@endif
                    </button>
                    <span class="topbar-divider"></span>
                    @auth
                        <span class="avatar avatar-top">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn (string $part): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->take(2)->implode('') }}</span>
                        <div class="topbar-user"><strong>{{ $user->name }}</strong><span>{{ $roleLabel }}</span></div>
                    @else
                        <a class="button button-secondary button-small" href="{{ route('login') }}">Sign in</a>
                    @endauth
                </div>
            </header>

            <main class="page-content">
                @if (session('success'))
                    <div class="toast toast-success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="toast toast-error" role="alert">{{ $errors->first() }}</div>
                @endif

                @include('greenfund.pages.'.$page)

                <footer class="page-footer"><span>ЖасылҚор · Kyzylorda City Environmental Operations</span><span>Sample data for local demonstration <i>·</i> MVP</span></footer>
            </main>
        </div>
    </div>

    @include('greenfund.modals')

    <script>
        window.greenFundData = @js([
            'page' => $page,
            'userId' => $user?->id,
            'role' => $user?->role->value,
            'canManageReports' => $canManageReports,
            'canManageTasks' => $canManageTasks,
            'canManageAssets' => $canManageAssets,
            'canCompleteTasks' => $canCompleteTasks,
            'reports' => $reports,
            'tasks' => $tasks,
            'assets' => $assets,
            'mapReports' => $mapReports,
            'mapAssets' => $mapAssets,
            'charts' => [
                'reportsByCategory' => $reportCategoryStats,
                'monthlyTrend' => $monthlyTrend,
                'assetConditions' => $conditionStats,
                'taskMetrics' => $taskMetrics,
                'reportMetrics' => $reportMetrics,
                'hotspots' => $hotspots,
            ],
        ]);
    </script>
    @livewireScripts
</body>
</html>
