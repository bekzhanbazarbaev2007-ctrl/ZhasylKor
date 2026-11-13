@extends('layouts.auth')

@section('title', 'Two-factor authentication')
@section('heading', 'Authentication code')
@section('description', 'Enter the code from your authenticator app or use one of your recovery codes.')

@section('content')
    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5">
        @csrf
        <label class="block space-y-2 text-sm font-medium" for="code">Authentication code
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" autofocus class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-mono text-lg tracking-[.35em]">
            @error('code')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="hidden space-y-2 text-sm font-medium" for="recovery_code" data-recovery-field>Recovery code
            <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code" disabled class="w-full rounded-lg border border-input bg-background px-3 py-2.5 font-normal">
            @error('recovery_code')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
        </label>
        <button class="w-full rounded-lg bg-emerald-700 px-4 py-2.5 font-medium text-white hover:bg-emerald-800" type="submit">Continue</button>
        <button class="w-full text-sm underline underline-offset-4" type="button" data-toggle-recovery>Use a recovery code instead</button>
    </form>
@endsection
