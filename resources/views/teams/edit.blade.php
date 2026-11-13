@extends('layouts.settings')

@section('title', $permissions->canUpdateTeam ? 'Edit '.$team['name'] : 'View '.$team['name'])
@section('heading', $team['name'])
@section('description', 'Manage team settings, members, and invitations.')

@section('content')
    <div class="space-y-7">
        @if ($permissions->canUpdateTeam)
            <section class="rounded-xl border border-border bg-card p-6">
                <h2 class="text-lg font-medium">Team settings</h2>
                <form method="POST" action="{{ route('teams.update', ['team' => $team['slug']]) }}" class="mt-4 flex max-w-xl flex-wrap items-end gap-3">
                    @csrf @method('PATCH')
                    <label class="min-w-52 flex-1 space-y-2 text-sm font-medium" for="team-name">Team name
                        <input id="team-name" name="name" value="{{ old('name', $team['name']) }}" required maxlength="255" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                        @error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <button class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white">Save</button>
                </form>
            </section>
        @endif

        <section class="rounded-xl border border-border bg-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-lg font-medium">Team members</h2><p class="mt-1 text-sm text-muted-foreground">Manage who belongs to this team.</p></div>
            </div>
            <div class="mt-4 divide-y divide-border">
                @foreach ($members as $member)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0" data-test="member-row">
                        <div><p class="font-medium">{{ $member['name'] }}</p><p class="text-sm text-muted-foreground">{{ $member['email'] }}</p></div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($member['role'] !== 'owner' && $permissions->canUpdateMember)
                                <form method="POST" action="{{ route('teams.members.update', ['team' => $team['slug'], 'user' => $member['id']]) }}" class="flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label class="sr-only" for="member-role-{{ $member['id'] }}">Role for {{ $member['name'] }}</label>
                                    <select id="member-role-{{ $member['id'] }}" name="role" class="rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                        @foreach ($availableRoles as $role)<option value="{{ $role['value'] }}" @selected($member['role'] === $role['value'])>{{ $role['label'] }}</option>@endforeach
                                    </select>
                                    <button class="rounded-lg border border-border px-3 py-2 text-sm">Update role</button>
                                </form>
                            @else
                                <span class="rounded-full bg-muted px-3 py-1.5 text-sm">{{ $member['role_label'] }}</span>
                            @endif
                            @if ($member['role'] !== 'owner' && $permissions->canRemoveMember)
                                <form method="POST" action="{{ route('teams.members.destroy', ['team' => $team['slug'], 'user' => $member['id']]) }}" onsubmit="return confirm('Remove this member from the team?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700">Remove</button></form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @if ($permissions->canCreateInvitation)
            <section class="rounded-xl border border-border bg-card p-6">
                <h2 class="text-lg font-medium">Invite a member</h2>
                <form method="POST" action="{{ route('teams.invitations.store', ['team' => $team['slug']]) }}" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <label class="min-w-52 flex-1 space-y-2 text-sm font-medium" for="invite-email">Email address
                        <input id="invite-email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                        @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-2 text-sm font-medium" for="invite-role">Role
                        <select id="invite-role" name="role" class="block rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                            @foreach ($availableRoles as $role)<option value="{{ $role['value'] }}">{{ $role['label'] }}</option>@endforeach
                        </select>
                    </label>
                    <button class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-medium text-white">Send invitation</button>
                </form>
            </section>
        @endif

        @if (count($invitations))
            <section class="rounded-xl border border-border bg-card p-6">
                <h2 class="text-lg font-medium">Pending invitations</h2>
                <div class="mt-3 divide-y divide-border">
                    @foreach ($invitations as $invitation)
                        <div class="flex items-center justify-between gap-4 py-3" data-test="invitation-row">
                            <div><p class="font-medium">{{ $invitation['email'] }}</p><p class="text-sm text-muted-foreground">{{ $invitation['role_label'] }}</p></div>
                            @if ($permissions->canCancelInvitation)
                                <form method="POST" action="{{ route('teams.invitations.destroy', ['team' => $team['slug'], 'invitation' => $invitation['code']]) }}" onsubmit="return confirm('Cancel this invitation?')">@csrf @method('DELETE')<button class="rounded-lg border border-border px-3 py-2 text-sm">Cancel</button></form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($permissions->canDeleteTeam && ! $team['isPersonal'])
            <section class="rounded-xl border border-red-200 bg-red-50 p-6">
                <h2 class="text-lg font-medium text-red-800">Delete team</h2>
                <p class="mt-1 text-sm text-red-800">Permanently delete this team. This action cannot be undone.</p>
                <form method="POST" action="{{ route('teams.destroy', ['team' => $team['slug']]) }}" class="mt-4 flex max-w-xl flex-wrap items-end gap-3">
                    @csrf @method('DELETE')
                    <label class="min-w-52 flex-1 space-y-2 text-sm font-medium" for="confirm-team-name">Type {{ $team['name'] }} to confirm
                        <input id="confirm-team-name" name="name" required class="w-full rounded-lg border border-input bg-white px-3 py-2.5 font-normal">
                        @error('name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <button class="rounded-lg bg-red-700 px-4 py-2.5 text-sm font-medium text-white">Delete team</button>
                </form>
            </section>
        @endif
    </div>
@endsection
