@extends('layouts.auth')

@section('title', 'Log in')
@section('heading', 'Log in to your account')
@section('description', 'Enter your email and password below to log in.')

@section('content')
    @if ($teamInvitation)
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
            You have been invited to join <strong>{{ $teamInvitation['teamName'] }}</strong>.
        </div>
    @endif
    <div class="space-y-4">
        <button type="button" class="w-full rounded-lg border border-border px-4 py-2.5 text-sm font-medium hover:bg-muted" data-passkey-login>
            Sign in with a passkey
        </button>
        <p class="text-center text-sm text-red-700" data-passkey-error role="alert"></p>
        <div class="flex items-center gap-3 text-xs uppercase text-muted-foreground"><span class="h-px flex-1 bg-border"></span>Or continue with email<span class="h-px flex-1 bg-border"></span></div>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-5 space-y-5">
        @csrf
        <label class="block space-y-2 text-sm font-medium" for="email">Email address
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email webauthn" required autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="email@example.com">
            @error('email')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block space-y-2 text-sm font-medium" for="password">Password
            <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal" placeholder="Password">
            @error('password')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember" value="1" class="rounded border-input"><span>Remember me</span></label>
            @if ($canResetPassword)<a class="underline underline-offset-4" href="{{ route('password.request') }}">Forgot password?</a>@endif
        </div>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Log in</button>
    </form>
    <p class="mt-6 text-center text-sm text-muted-foreground">Don't have an account?
        <a class="font-medium text-foreground underline underline-offset-4" href="{{ route('register', $teamInvitation ? ['invitation' => $teamInvitation['code']] : []) }}">Sign up</a>
    </p>
@endsection
