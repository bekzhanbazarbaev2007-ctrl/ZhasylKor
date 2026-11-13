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
    <main class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-6 py-12">
        <a href="{{ route('greenfund.screen', ['page' => 'dashboard']) }}" class="mb-8 inline-flex items-center gap-3 self-center font-semibold">
            <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-700 text-xl text-white" aria-hidden="true">Ж</span>
            <span><span class="block text-lg">ЖасылҚор</span><span class="block text-xs font-normal tracking-[.16em] text-muted-foreground">GREENFUND MAP</span></span>
        </a>
        <section class="rounded-2xl border border-border bg-card p-7 shadow-sm sm:p-9">
            <h1 class="text-2xl font-semibold tracking-tight">@yield('heading')</h1>
            <p class="mt-2 text-sm text-muted-foreground">@yield('description')</p>

            @if (session('status'))
                <p class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
                    <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="mt-6">@yield('content')</div>
        </section>
        <p class="mt-6 text-center text-xs text-muted-foreground">Kyzylorda City Environmental Operations</p>
    </main>
</body>
</html>
