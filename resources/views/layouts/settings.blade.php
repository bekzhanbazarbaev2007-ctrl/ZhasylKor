<!doctype html>
<html lang="en" class="{{ $appearance === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · ЖасылҚор</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <header class="border-b border-border bg-card">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('greenfund.screen', ['page' => 'dashboard']) }}" class="font-semibold">ЖасылҚор <span class="ml-2 text-xs font-normal tracking-widest text-muted-foreground">GREENFUND MAP</span></a>
            <div class="flex items-center gap-4 text-sm">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="underline underline-offset-4">Log out</button></form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-6 py-8">
        <div class="mb-7">
            <h1 class="text-2xl font-semibold">@yield('heading')</h1>
            <p class="mt-1 text-sm text-muted-foreground">@yield('description')</p>
        </div>
        @if (session('success'))<p class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" role="status">{{ session('success') }}</p>@endif
        @if ($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="grid gap-8 md:grid-cols-[200px_1fr]">
            <nav class="flex gap-2 overflow-auto text-sm md:flex-col" aria-label="Settings">
                <a class="rounded-lg px-3 py-2 hover:bg-muted" href="{{ route('profile.edit') }}">Profile</a>
                <a class="rounded-lg px-3 py-2 hover:bg-muted" href="{{ route('security.edit') }}">Security</a>
                <a class="rounded-lg px-3 py-2 hover:bg-muted" href="{{ route('appearance.edit') }}">Appearance</a>
                <a class="rounded-lg px-3 py-2 hover:bg-muted" href="{{ route('teams.index') }}">Teams</a>
            </nav>
            <section class="min-w-0">@yield('content')</section>
        </div>
    </main>
</body>
</html>
