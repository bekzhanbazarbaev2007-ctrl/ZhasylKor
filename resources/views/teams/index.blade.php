@extends('layouts.settings')

@section('title', 'Teams')
@section('heading', 'Teams')
@section('description', 'Manage your teams and team memberships.')

@section('content')
    <div class="space-y-4">
        <section class="rounded-xl border border-border bg-card p-6">
            <h2 class="text-lg font-medium">Create a team</h2>
            <form method="POST" action="{{ route('teams.store') }}" class="mt-4 flex max-w-xl flex-wrap items-end gap-3">
                @csrf
                <label class="min-w-52 flex-1 space-y-2 text-sm font-medium" for="team-name">Team name
                    <input id="team-name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                    @error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                </label>
                <button class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white">New team</button>
            </form>
        </section>

        @forelse ($teams as $team)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-border bg-card p-5" data-test="team-row">
                <div>
                    <h2 class="font-medium">{{ $team->name }} @if ($team->isPersonal)<span class="ml-2 rounded-full bg-muted px-2 py-1 text-xs">Personal</span>@endif</h2>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $team->roleLabel }} @if ($team->isCurrent) · Current team @endif</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if (! $team->isPersonal && $team->role !== 'owner')
                        <form method="POST" action="{{ route('teams.leave', ['team' => $team->slug]) }}" onsubmit="return confirm('Leave this team?')">@csrf @method('DELETE')<button class="rounded-lg border border-border px-3 py-2 text-sm">Leave</button></form>
                    @endif
                    @if (! $team->isCurrent)
                        <form method="POST" action="{{ route('teams.switch', ['team' => $team->slug]) }}">@csrf<button class="rounded-lg border border-border px-3 py-2 text-sm">Switch to team</button></form>
                    @endif
                    <a href="{{ route('teams.edit', ['team' => $team->slug]) }}" class="rounded-lg border border-border px-3 py-2 text-sm">{{ $team->role === 'member' ? 'View' : 'Manage' }}</a>
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-border bg-card p-8 text-center text-sm text-muted-foreground">You don't belong to any teams yet.</p>
        @endforelse
    </div>
@endsection
