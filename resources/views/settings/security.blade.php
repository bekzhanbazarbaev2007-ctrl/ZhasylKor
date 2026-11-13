@extends('layouts.settings')

@section('title', 'Security settings')
@section('heading', 'Security settings')
@section('description', 'Protect your account with a strong password, two-factor authentication, and passkeys.')

@section('content')
    <section class="rounded-xl border border-border bg-card p-6">
        <h2 class="text-lg font-medium">Update password</h2>
        <form method="POST" action="{{ route('user-password.update') }}" class="mt-5 max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <label class="block space-y-2 text-sm font-medium" for="current_password">Current password
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('current_password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block space-y-2 text-sm font-medium" for="password">New password
                <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block space-y-2 text-sm font-medium" for="password_confirmation">Confirm new password
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
            </label>
            <button class="rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white" type="submit">Save password</button>
        </form>
    </section>

    @if ($canManageTwoFactor)
        <section class="mt-6 rounded-xl border border-border bg-card p-6">
            <h2 class="text-lg font-medium">Two-factor authentication</h2>
            <p class="mt-1 text-sm text-muted-foreground">Use a time-based code from an authenticator app to secure sign-in.</p>
            @if ($twoFactorEnabled)
                <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">Two-factor authentication is enabled.</p>
                <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">@csrf @method('DELETE')<button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700">Disable 2FA</button></form>
                <div class="mt-5">
                    <button class="rounded-lg border border-border px-4 py-2 text-sm" type="button" data-load-recovery-codes>View recovery codes</button>
                    <div class="mt-3 hidden rounded-lg bg-muted p-4 font-mono text-sm" data-recovery-codes aria-live="polite"></div>
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-3">@csrf<button class="text-sm underline underline-offset-4">Regenerate codes</button></form>
                </div>
            @elseif ($twoFactorSetupPending)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <strong>Finish setting up two-factor authentication.</strong>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button class="rounded-lg border border-amber-300 px-3 py-2" type="button" data-load-two-factor>Load QR code</button>
                        <span class="self-center text-xs" data-two-factor-error role="alert"></span>
                    </div>
                    <div class="mt-3 max-w-56 rounded bg-white p-2" data-two-factor-qr></div>
                    <p class="mt-2 text-xs">Manual setup key: <code data-two-factor-secret>Not loaded</code></p>
                </div>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 max-w-sm space-y-3">
                    @csrf
                    <label class="block space-y-2 text-sm font-medium" for="two-factor-code">6-digit authentication code
                        <input id="two-factor-code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
                        @error('code')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <button class="rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white">Confirm 2FA</button>
                </form>
            @else
                <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">@csrf<button class="rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white">Enable 2FA</button></form>
            @endif
        </section>
    @endif

    @if ($canManagePasskeys)
        <section class="mt-6 rounded-xl border border-border bg-card p-6">
            <h2 class="text-lg font-medium">Passkeys</h2>
            <p class="mt-1 text-sm text-muted-foreground">Sign in securely without a password using a device passkey.</p>
            <div class="mt-4 divide-y divide-border rounded-lg border border-border">
                @forelse ($passkeys as $passkey)
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div><strong>{{ $passkey['name'] }}</strong><p class="mt-1 text-xs text-muted-foreground">Added {{ $passkey['created_at_diff'] }}@if ($passkey['last_used_at_diff']) · Last used {{ $passkey['last_used_at_diff'] }}@endif</p></div>
                        <form method="POST" action="{{ route('passkey.destroy', ['passkey' => $passkey['id']]) }}" onsubmit="return confirm('Remove this passkey?')">@csrf @method('DELETE')<button class="text-sm text-red-700 underline underline-offset-4">Remove</button></form>
                    </div>
                @empty
                    <p class="p-5 text-center text-sm text-muted-foreground">No passkeys yet. Add one to sign in without a password.</p>
                @endforelse
            </div>
            <form class="mt-4 flex max-w-lg flex-wrap gap-3" data-passkey-register>
                <label class="sr-only" for="passkey-name">Passkey name</label>
                <input id="passkey-name" name="name" required maxlength="100" placeholder="e.g. Work laptop" class="min-w-48 flex-1 rounded-lg border border-input bg-background px-3 py-2.5">
                <button class="rounded-lg border border-border px-4 py-2.5 font-medium" type="submit">Add passkey</button>
                <p class="w-full text-sm text-red-700" data-passkey-error role="alert"></p>
            </form>
        </section>
    @endif
@endsection
