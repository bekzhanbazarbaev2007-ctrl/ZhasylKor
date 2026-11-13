@extends('layouts.settings')

@section('title', 'Dashboard')
@section('heading')
    Welcome, {{ auth()->user()->name }}
@endsection
@section('description', 'Your team workspace and pending invitations.')

@section('content')
    <div class="space-y-6">
        <section class="rounded-xl border border-border bg-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-medium">ЖасылҚор operations</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Manage reports, field tasks, and Kyzylorda's green assets.</p>
                </div>
                <a href="{{ route('greenfund.screen', ['page' => 'dashboard']) }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white">Open GreenFund Map</a>
            </div>
        </section>

        @if ($pendingInvitations->isNotEmpty())
            <section class="rounded-xl border border-border bg-card p-6">
                <h2 class="text-lg font-medium">Pending team invitations</h2>
                <div class="mt-4 divide-y divide-border">
                    @foreach ($pendingInvitations as $invitation)
                        <div class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                            <p class="text-sm"><strong>{{ $invitation['team']['name'] }}</strong><span class="text-muted-foreground"> · invited by {{ $invitation['inviterName'] }}</span></p>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('invitations.accept', ['invitation' => $invitation['code']]) }}">@csrf<button class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white">Accept</button></form>
                                <form method="POST" action="{{ route('invitations.decline', ['invitation' => $invitation['code']]) }}">@csrf @method('DELETE')<button class="rounded-lg border border-border px-3 py-2 text-sm">Decline</button></form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-xl border border-border bg-card p-6">
            <h2 class="text-lg font-medium">Team management</h2>
            <p class="mt-1 text-sm text-muted-foreground">Manage your team memberships and invitations.</p>
            <a href="{{ route('teams.index') }}" class="mt-4 inline-flex rounded-lg border border-border px-4 py-2.5 text-sm font-medium hover:bg-muted">View teams</a>
        </section>
    </div>
@endsection
